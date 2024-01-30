<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Process;
use Illuminate\Http\Request;

class TestingController extends Controller
{
    public function test(Request $request) {
        // Get the Python script path from the .env file
        $scriptPath = env('PUBLISHER_SCRIPT');

        // Extract the 'state' query parameter from the request
        $argument = $request->query('state');

        // Ensure the script path and argument are safely escaped for shell command
        $safeScriptPath = escapeshellarg($scriptPath);
        $safeArgument = escapeshellarg($argument);

        // Construct the command
        $command = "python " . $safeScriptPath . " " . $safeArgument;

        // Execute the command
        $result = shell_exec($command);
        return $result;
    }
}
