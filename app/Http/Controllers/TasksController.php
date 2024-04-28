<?php

namespace App\Http\Controllers;

use App\Models\Tasks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class TasksController extends Controller
{
    public function create(Request $request){
        // Ensure there's an authenticated user
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Not authenticated'], 401); 
        }

        $task = new Tasks();
        $task->name = $request->TASK;
        $task->user_id = $user->id; 
        $task->map = $request->MAP;
        $task->pickupNode = $request->pickUpNode;
        $task->dropoffNode = $request->dropOffNode;
        $task->taskTime = $request->TaskTime;
        $task->save();

        $task = $user->task; 

        return response()->json($task, 201);
    }

    public function getTasks(){
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Not authenticated'], 401); 
        }

        $tasks = $user->tasks;
        return response()->json($tasks);
    }
}
