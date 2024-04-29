<?php

namespace App\Http\Controllers;

use App\Models\Robot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RobotController extends Controller
{
    public function create(Request $request){
        // Ensure there's an authenticated user
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Not authenticated'], 401); 
        }

        $robot = new Robot();
        $robot->name = $request->name;
        $robot->user_id = $user->id; 
        $robot->IP = $request->IP;
        $robot->save();

        $robots = $user->robots; 

        return response()->json($robots, 201);
    }

    public function getRobots(){
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Not authenticated'], 401); 
        }

        $robots = $user->robots;
        return response()->json($robots);
    }

    public function deleteRobot(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Not authenticated'], 401);
        }

        $robotId = $request->id; 

        if (!$robotId) {
            return response()->json(['message' => 'Robot ID is required'], 400);
        }

        $robot = Robot::where('id', $robotId)->where('user_id', $user->id)->first();

        if (!$robot) {
            return response()->json(['message' => 'Robot not found or access denied'], 404);
        }

        $robot->delete();

        $robots = $user->robots;
        // Return a success response
        return response()->json([
            'message' => 'Robot deleted successfully',
            'remainingRobots' => $robots
        ], 200);
    }

    public function editRobot(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Not authenticated'], 401);
        }

        $robotId = $request->Robot_id;

        if (!$robotId) {
            return response()->json(['message' => 'Robot ID is required'], 400);
        }

        $robot = Robot::where('id', $robotId)->where('user_id', $user->id)->first();

        if (!$robot) {
            return response()->json(['message' => 'Robot not found or access denied'], 404);
        }

        // Update the robot details from the request
        $robot->name =  $request->name;
        $robot->IP = $request->IP;

        $robot->save();

        $robots = $user->robots;

        return response()->json([
            'message' => 'Robot updated successfully',
            'robots' => $robots
        ], 200);
    }
}
