<?php

namespace App\Services\WorkerServices;

use App\Models\Worker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WorkerLoginService
{
    protected Worker $model;

    public function __construct()
    {
        $this->model = new Worker();
    }

    public function validation($request)
    {
        $data = Validator::make($request->all(), $request->rules());
        if ($data->fails()) {
            return response()->json($data->errors(), 422);
        }
        return $data;
    }

    public function IsValidData($data)
    {

        if (!$token = auth()->guard('worker')->attempt($data->validated())) {
            return false;
        }
        return $token;
    }

    public function getStatus($request)
    {
        $worker = Worker::whereEmail($request->email)->first();
        return $worker->status;
    }

    public function isVerified($request): bool
    {
        $worker = Worker::whereEmail($request->email)->first();
        return (bool)$worker->verified_at;
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


    public function login($request)
    {

        $data = $this->validation($request);
        $token = $this->IsValidData($data);

        if (!$token) {
            return response()->json(['error' => 'Wrong email or password'], 401);
        } elseif (!$this->getStatus($request)) {
            return response()->json(['message' => 'Your account is pending'], 403);
        } elseif (!$this->isVerified($request)) {
            return response()->json(['message' => 'Please check your email to verify your account'], 403);
        }
        return $this->createNewToken($token);


    }
}
