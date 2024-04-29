<?php

namespace App\Http\Controllers;

use App\Models\Tasks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TasksController extends Controller
{
    public function create(Request $request) {
        $user = Auth::user(); // Ensure the user is authenticated
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

        $tasks = $user->tasks; 

        return response()->json($tasks, 201); // Return the created task with a 201 status
    }

    public function getTasks() {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Not authenticated'], 401); 
        }

        $tasks = $user->tasks;
        return response()->json($tasks);
    }

    public function deleteTask(Request $request) {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Not authenticated'], 401);
        }

        $taskId = $request->id; // Ensure task ID is provided
        if (is_null($taskId)) {
            return response()->json(['message' => 'Task ID is required'], 400);
        }

        $task = Tasks::where('id', $taskId)->where('user_id', $user->id)->first();
        if (is_null($task)) {
            return response()->json(['message' => 'Task not found or access denied'], 404); // Handle not found or unauthorized
        }

        $task->delete();

        $remainingTasks = $user->tasks; // Retrieve remaining tasks after deletion
        return response()->json([
            'message' => 'Task deleted successfully',
            'remainingTasks' => $remainingTasks
        ], 200); // Return success with remaining tasks
    }
}