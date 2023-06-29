<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Http\Controllers\Api\V1\BaseController as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseController
{
    /**
     * Create User
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function createUser(Request $request)
    {
        try {
            //Validated
            $validator = Validator::make($request->all(),
                [
                    'name' => 'required',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required'
                ]);

            if($validator->fails()){
                return $this->sendError('Validation Error.', $validator->errors(), 401);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            $success['token'] =  $user->createToken('API TOKEN')->plainTextToken;
            $success['name'] =  $user->name;

            return $this->sendResponse($success, 'User Created Successfully');
        } catch (\Throwable $th) {
            return $this->sendError('Server Error.', $th->getMessage(), 500);
        }
    }

    /**
     * Login The User
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function loginUser(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),
                [
                    'email' => 'required|email',
                    'password' => 'required'
                ]);

            if($validator->fails()){
                return $this->sendError('Validation Error.', $validator->errors(), 401);
            }

            if(!Auth::attempt($request->only(['email', 'password']))){
                return $this->sendError('Email & Password does not match with our record.', $validator->errors(), 401);
            }

            $user = User::where('email', $request->email)->first();

            $success['token'] =  $user->createToken('API TOKEN')->plainTextToken;

            return $this->sendResponse($success, 'User Logged In Successfully');

        } catch (\Throwable $th) {
            return $this->sendError('Server Error.', $th->getMessage(), 500);
        }
    }

}
