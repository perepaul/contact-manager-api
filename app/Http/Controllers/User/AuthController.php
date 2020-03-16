<?php

namespace App\Http\Controllers\User;

use JWTAuth;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;


class AuthController extends Controller
{

    protected $user;
    public function __construct()
    {
        $this->user = new User();

    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(),
        [
            'firstname'=>'required|string',
            'lastname'=>'required|string',
            'email'=>'required|email',
            'password'=>'required|string|min:6'
        ]);

        if($validator->fails()){
            return response()->json([
                'success'=>false,
                'message'=>$validator->messages()->toArray(),
            ],400);
        }

        $check_email = $this->user->where('email',$request->email)->count();
        if($check_email > 0)
        {
            return response()->json([
                'success'=>false,
                'message'=>'this email already exist, please try another one',
            ],400);
        }

        $registerComplete = $this->user::create([
            'firstname'=>$request->firstname,
            'lastname'=>$request->lastname,
            'email'=>$request->email,
            'password'=>$request->password
        ]);


        if($registerComplete)
        {
           return $this->login($request);
        }

    }

    public function login(Request $request)
    {
        $input = $request->only('email','password');
        $validator = Validator::make($request->only('email','password'),
        [
            'email'=>'required|email',
            'password'=>'required|string|min:6'
        ]);

        if($validator->fails()){
            return response()->json([
                'success'=>false,
                'message'=>$validator->messages()->toArray(),
            ],400);
        }

        $jwt_token = null;

       if(!$jwt_token = auth('users')->attempt($input))
       {
           return response()->json([
               'success'=>false,
               'message'=>'Invalid email or password'
           ]);
       }

       return response()->json(
           [
               'success'=>true,
               'token'=>$jwt_token
           ]);

    }
}
