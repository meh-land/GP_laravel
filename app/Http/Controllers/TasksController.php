<?php

namespace App\Http\Controllers;

use App\Models\Tasks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Storage;
use App\Models\Maps;

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

    public function getTasks()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Not authenticated'], 401);
        }

        $tasks = $user->tasks;

        foreach ($tasks as &$task) {
            $mapId = $task->map;
            $map = Maps::where('id', $mapId)->firstOrFail();

            // Define the path to the file
            $path = 'maps/' . $map->file;

            // Check if the file exists
            if (!Storage::disk('local')->exists($path)) {
                return response()->json(['message' => 'File not found'], 404);
            }

            // Read the content of the file
            $content = Storage::disk('local')->get($path);
            $mapData = json_decode($content, true);

            // Build an associative array of node ID to node name
            $nodeLabels = [];
            foreach ($mapData['nodes'] as $node) {
                $nodeLabels[$node['id']] = $node['data']['label'];
            }

            // Set map to map name
            $task->map = $map->name;
            // Replace pickupNode
            if (isset($nodeLabels[$task->pickupNode])) {
                $task->pickupNode = $nodeLabels[$task->pickupNode];
            }
            // Replace dropoffNode
            if (isset($nodeLabels[$task->dropoffNode])) {
                $task->dropoffNode = $nodeLabels[$task->dropoffNode];
            }
        }

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