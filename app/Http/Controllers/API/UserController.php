<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\User;

class UserController extends Controller
{
    public function login(Request $request)
    {
        // email
        // password

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['msg' => 'You have some error', 'data' => $validator->errors(), 'error' => true], 400);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            $data['token'] =  $user->createToken('Learning')->accessToken;
            $data['user'] = $user;

            
            return response()->json(['msg' => 'You have successfully login', 'data' => $data, 'error' => false],200);
        }else{
          return response()->json(['msg' => 'Sorry your cradensial is wrong', 'data' => [], 'error' => true],400);
        }

    }

    public function register(Request $request)
    {
        // name
        // email
        // password

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users|max:255',
            'password' => 'required|confirmed|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['msg' => 'You have some error', 'data' => $validator->errors(), 'error' => true], 400);
        }
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            $data['token'] =  $user->createToken('Restaurent')->accessToken;
            $data['user'] = $user;
            return response()->json(['msg' => 'You have sucessfully register', 'data' => $data, 'error' => false],200);
        }else{
            return response()->json(['msg' => 'Please try again', 'data' => [], 'error' => true],402);
        }


    }
    
    public function logout(Request $request)
    {
        $token = $request->user()->token();
        $token->revoke();
                  
        return response()->json(['msg' => 'Logout Sucessful', 'data' => [], 'error' => false],200);   
    }

    public function details()
    {
        $user = Auth::user();
        $data = [];

        $data['user'] = $user;
        
        return response()->json(['msg' => 'Hello '.$user->name, 'data' => $data, 'error' => false],200);
    }
}
