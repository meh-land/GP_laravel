<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class PassportAuthController extends Controller
{
        public function register(Request $request){
        $this->validate($request, [
            'name'=>'required',
            'email'=>'required',
            'password'=>'required|min:8'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $token = $user->createToken('gp')->accessToken;

        return response()->json(['token'=>$token],200);
    }

    public function login(Request $request){
        $data = [
            'email' => $request->email,
            'password' => $request->password
        ];

        if(auth()->attempt($data)){
            $user = auth()->user();
            $token = $user->createToken($user->name)->accessToken;
            return response()->json(['token' => $token],200);
        } else{
            return response()->json(['error' => 'Unauthorized'],401);
        }

    }

    public function show(){
        $user = auth()->user();
        return response()->json(['user' => $user],200);
    }

    public function update(){
        $user = auth()->user();
        return response()->json(['user' => $user],200);
    }
}
