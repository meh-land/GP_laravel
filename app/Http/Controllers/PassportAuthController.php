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

    public function update(Request $request) {
        // Validate the request inputs
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string',
            'email' => 'sometimes|required|email|unique:users,email,' . auth()->id(),
            'password' => 'sometimes|required|min:6',
        ]);

        $keys = ['name', 'email', 'password'];
        $updateData = [];

        foreach ($keys as $key) {
            if (isset($validatedData[$key])) {
                $updateData[$key] = $key == 'password' ? bcrypt($validatedData[$key]) : $validatedData[$key];
            }
        }

        if (empty($updateData)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid data provided for update',
            ]);
        }

        $user = auth()->user();
        $user->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
        ]);
    }

    public function delete(Request $request) {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }


        try {
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the user',
            ], 500);
        }
    }
}
