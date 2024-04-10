<?php

namespace App\Http\Controllers;

use App\Models\Maps;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class MapsController extends Controller
{
    public function create(Request $request){
        // Ensure there's an authenticated user
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Not authenticated'], 401); 
        }

        // Assume $mapContent contains the JSON content of your map
        // This might be generated based on some logic or data in your application
        $mapContent = json_encode([
            // Example map data
            'name' => $request->name,
            'nodes' => $request->nodes,
            'edges' => $request->edges,
        ]);

        // Sanitize the map name to create a safe filename
        $safeName = preg_replace('/[^A-Za-z0-9\-_]/', '', $request->name);
        $filename = $safeName . '.json';

        // Define the path to save the file (within the storage/app directory)
        $path = 'maps/' . $filename;

        // Use Laravel's Storage to save the content to a .json file
        Storage::disk('local')->put($path, $mapContent);

        // If you want to save the file outside of the storage/app directory, use absolute path
        // and PHP's file_put_contents (ensure proper permissions for the web server to write to the target directory)
        // $absolutePath = '/home/maps/' . $filename;
        // file_put_contents($absolutePath, $mapContent);

        // Create a new map record
        $map = new Maps();
        $map->name = $request->name;
        $map->user_id = $user->id;
        $map->file = $filename; // Save the filename or path in the database
        $map->save();

        // Get updated maps
        $maps = $user->maps;

        return response()->json($maps, 201);
    }

    public function getMap(Request $request)
    {
        $name = $request->name;
        // Find the map by name or return a 404 error if not found
        $map = Maps::where('name', $name)->firstOrFail();

        // Define the path to the file
        $path = 'maps/' . $map->file;

        // Check if the file exists
        if (!Storage::disk('local')->exists($path)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        // Read the content of the file
        $content = Storage::disk('local')->get($path);

        // Return the content of the map file as JSON
        return response()->json(json_decode($content, true));
    }
}
