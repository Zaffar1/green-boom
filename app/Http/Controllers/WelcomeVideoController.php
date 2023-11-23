<?php

namespace App\Http\Controllers;

use App\Models\WelcomeVideo;
use Illuminate\Http\Request;

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

    public function uploadWelcomeVideo(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'file' => 'required|mimes:mp4,mov,avi|max:204800', // Adjust the max size as needed
        ]);

        try {
            $videoPath = $request->file('file')->store('public/videos');
            $video = new WelcomeVideo();
            $video->file = $videoPath;
            $video->title = $request->title;
            $video->description = $request->description;
            $video->status = "Active";
            $video->save();

            return response()->json(["message" => "Video uploaded successfully."]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function deleteWelcomeVideo(Request $request, $id)
    {
        try {
            $video = WelcomeVideo::find($id);
            $video->delete();
            return response()->json(["message" => "Welcome video successfully deleted"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }
}
