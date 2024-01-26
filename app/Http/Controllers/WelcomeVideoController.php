<?php

namespace App\Http\Controllers;

use App\Models\WelcomeVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WelcomeVideoController extends Controller
{

    public function allWelcomeVideos()
    {
        try {
            $all_videos = WelcomeVideo::orderBy('id', 'DESC')->get();
            return response()->json(["all_welcome_videos" => $all_videos]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function welcomeVideo()
    {
        try {
            $welcome_video = WelcomeVideo::where('status', 'Active')->latest()->first();
            return response()->json(["welcome_video" => $welcome_video]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }


    public function uploadWelcomeVideo(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'file' => 'required|mimes:mp4,mov,avi', // Adjust the max size as needed
        ]);

        try {
            // $baseUrl = "http://localhost:8000/";
            // $new_name = time() . '.' . $request->file->extension();
            // $request->file->move(public_path('storage/videos'), $new_name);
            // $path = "storage/videos/$new_name";

            // $fullUrl = url($baseUrl . $path);
            $new_name = time() . '.' . $request->file->extension();
            $request->file->move(public_path('storage/videos'), $new_name);
            $path = "storage/videos/$new_name";
            // $videoPath = $request->file('file')->store('public/videos');
            $video = new WelcomeVideo();
            $video->file = $path;
            $video->title = $request->title;
            $video->description = $request->description;
            $video->status = "Active";
            $video->save();

            return response()->json(["message" => "Video uploaded successfully."]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function updateWelcomeVideo(Request $request)
    {
        $request->validate([
            'video_id' => 'required',
            'title' => 'required',
            'description' => 'required',
            // 'file' => 'required|mimes:mp4,mov,avi|max:204800', // Adjust the max size as needed
        ]);

        try {
            // Find the WelcomeVideo by ID
            // $video = WelcomeVideo::find($id);
            $video = WelcomeVideo::find($request->video_id);

            if (!$video) {
                return response()->json(["error" => "Video not found"], 404);
            }

            // Update fields
            $video->title = $request->title;
            $video->description = $request->description;

            // If a new file is provided, update the file
            if ($request->hasFile('file')) {
                // Delete the old file from storage
                // Storage::delete($video->file);

                // Use unlink for direct file deletion
                if (file_exists($video->file)) {
                    unlink($video->file);
                }
                // Store the new file
                // $video->file = $request->file('file')->store('public/videos');
                $new_name = time() . '.' . $request->file->extension();
                $request->file->move(public_path('storage/videos'), $new_name);
                $path = "storage/videos/$new_name";
                $video->file = $path;
            }

            // Save the changes
            $video->save();

            return response()->json(["message" => "Video updated successfully."]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function deleteWelcomeVideo(Request $request, $id)
    {
        try {
            $video = WelcomeVideo::find($id);
            if (!$video) {
                return response()->json(["error" => "Video not found"], 404);
            }
            $filePath = $video->file;
            // return response()->json(["file" => $filePath]);
            $video->delete();
            // Use unlink for direct file deletion
            // if (file_exists($filePath)) {
            //     unlink($filePath);
            // }
            Storage::delete($filePath);
            return response()->json(["message" => "Welcome video successfully deleted"], 200);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function videoStatus($id)
    {
        try {
            $video = WelcomeVideo::find($id);
            if (!$video)
                return response()->json(["message" => "Invalid video"]);
            if ($video->status == "Active") {
                $video->status = "InActive";
            } else {
                $video->status = "Active";
            }
            $video->save();
            return response()->json(["message" => "Welcome video status changed", "status" => $video->status], 200);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }
}
