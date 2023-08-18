<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\Admin;
use App\Models\Worker;
use App\Services\WorkerServices\WorkerRegisterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\WorkerServices\WorkerLoginService;

class WorkerController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:worker', ['except' => ['login', 'register']]);
    }

    public function login(LoginRequest $request)
    {
        return (new WorkerLoginService())->login($request);

    }

    public function register(Request $request)
    {
        return (new WorkerRegisterService())->register($request);


    }


    public function logout()
    {
        auth()->guard('worker')->logout();
        return response()->json(['message' => 'User successfully signed out']);
    }


    public function refresh()
    {
        return $this->createNewToken(auth()->guard('worker')->refresh());
    }


    public function userProfile()
    {
        return response()->json(auth()->guard('worker')->user());
    }

    public function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->guard('worker')->user()
        ]);
    }

}
