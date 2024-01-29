<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;

class PassportAuthController extends Controller
{
    public function register(Request $request){
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $token = $user->createToken('gp')->accessToken;

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'token' => $token,
        ];

        return response()->json([
            'user' => $userData
        ], 200);
    }

    public function login(Request $request){
        $data = [
            'email' => $request->email,
            'password' => $request->password
        ];

        if(auth()->attempt($data)){
            $user = auth()->user();
            $token = $user->createToken($user->name)->accessToken;

            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'token' => $token,
            ];

            return response()->json([
                'user' => $userData
            ], 200);
        } else{
            return response()->json(['error' => 'Unauthorized'], 401);
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
            'old_password' => 'sometimes|required_with:password',
        ]);

        $user = auth()->user();

        // Check if old password is correct before updating to a new one
        if (isset($validatedData['password'])) {
            if (!Hash::check($validatedData['old_password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Incorrect old password',
                ]);
            }

            $validatedData['password'] = bcrypt($validatedData['password']);
        }

        $keys = ['name', 'email', 'password'];
        $updateData = [];

        foreach ($keys as $key) {
            if (isset($validatedData[$key])) {
                $updateData[$key] = $validatedData[$key];
            }
        }

        if (empty($updateData)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid data provided for update',
            ]);
        }

        $user->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
        ]);
    }

    public function delete(Request $request) {
         // Validate the request inputs
        $validatedData = $request->validate([
            'old_password' => 'sometimes|required_with:password',
        ]);
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        if (!Hash::check($validatedData['old_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect old password',
            ]);
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
