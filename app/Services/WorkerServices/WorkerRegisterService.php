<?php

namespace App\Services\WorkerServices;


use App\Models\Worker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WorkerRegisterService
{

    protected Worker $model;

    public function __construct()
    {
        $this->model = new Worker();
    }

    public function validation($request)
    {
        $data = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:workers',
            'password' => 'required|string|confirmed|min:6',
            'phone' => 'required|string',
            'photo' => 'required|image|mimes:jpg,png,jpeg',
            'location' => 'required|string',]);
        if ($data->fails()) {
            return response()->json($data->errors(), 422);
        }
        return $data;
    }


    public function store($data, $request)
    {
        return $this->model->create(array_merge(
            $data->validated(),
            [
                'password' => bcrypt($request->password),
                'photo' => $request->file('photo')->store('workers'),
            ]
        ));
    }

    public function generateToken($request)
    {
        $token = substr(md5(rand(0, 9) . $request->email . time()), 0, 32);
        $worker = $this->model->whereEmail($request->email)->first();
        $worker->verification_token = $token;
        $worker->save();
    }

    public function register($request)
    {
        DB::beginTransaction();
        try {
            $data = $this->validation($request);
            $user = $this->store($data, $request);
            $this->generateToken($request);
            DB::commit();
            return response()->json([
                'message' => 'User successfully registered',
                'worker' => $user
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }
}
