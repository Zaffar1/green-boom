<?php

namespace App\Http\Controllers;

use App\Models\WelcomeVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WelcomeVideoController extends Controller
{
    /**
     * Retrieve all welcome videos.
     *
     * This function retrieves all welcome videos from the database and orders them
     * by their IDs in descending order.
     * It then returns a JSON response containing all the retrieved welcome videos.
     * If an error occurs during the process, it catches the exception and returns
     * a JSON response with a 400 status containing the error message.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing all the welcome videos.
     */

    public function allWelcomeVideos()
    {
        try {
            $all_videos = WelcomeVideo::orderBy('id', 'DESC')->get();
            return response()->json(["all_welcome_videos" => $all_videos]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * Retrieve the active welcome video.
     *
     * This function retrieves the latest active welcome video from the database.
     * It then returns a JSON response containing the retrieved welcome video.
     * If an error occurs during the process, it catches the exception and returns
     * a JSON response with a 400 status containing the error message.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing the active welcome video.
     */
    public function welcomeVideo()
    {
        try {
            $welcome_video = WelcomeVideo::where('status', 'Active')->latest()->first();
            return response()->json(["welcome_video" => $welcome_video]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * Upload a welcome video.
     *
     * This function validates the incoming request data, including the title, description,
     * and file (video). It then attempts to store the uploaded video file and create a new
     * WelcomeVideo record in the database with the provided details. Upon successful upload,
     * it returns a JSON response with a success message. If an error occurs during the process,
     * it catches the exception and returns a JSON response with a 400 status containing the
     * error message.
     *
     * @param  \Illuminate\Http\Request  $request The incoming request containing the video data.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the success or failure of the video upload.
     */

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
            $path = $request->file('file')->storeAs('videos', $new_name, 's3');
            // $request->file->move(public_path('storage/videos'), $new_name);
            // $path = "storage/videos/$new_name";
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

    /**
     * Update the details of a welcome video.
     *
     * This function validates the incoming request data, including the video ID, title, and description.
     * It then attempts to find the WelcomeVideo record by the provided ID and updates its fields accordingly.
     * If a new file is provided, it replaces the existing file with the new one. Upon successful update,
     * it returns a JSON response with a success message. If the video is not found or an error occurs
     * during the process, it returns a JSON response with a 404 or 400 status, respectively, containing
     * the appropriate error message.
     *
     * @param  \Illuminate\Http\Request  $request The incoming request containing the updated video data.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the success or failure of the video update.
     */

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
                // $request->file->move(public_path('storage/videos'), $new_name);
                $path = $request->file('file')->storeAs('videos', $new_name, 's3');
                // $path = "storage/videos/$new_name";
                $video->file = $path;
            }

            // Save the changes
            $video->save();

            return response()->json(["message" => "Video updated successfully."]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * Delete a welcome video.
     *
     * This function deletes a welcome video based on the provided video ID.
     * It attempts to find the WelcomeVideo record by the given ID and deletes it.
     * Additionally, it deletes the associated file from storage if it exists.
     * Upon successful deletion, it returns a JSON response with a success message.
     * If the video is not found or an error occurs during the process, it returns
     * a JSON response with a 404 or 400 status, respectively, containing the
     * appropriate error message.
     *
     * @param  \Illuminate\Http\Request  $request The incoming request object.
     * @param  int  $id The ID of the welcome video to delete.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the success or failure of the video deletion.
     */

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
            if (file_exists($filePath)) {
                unlink($filePath);
            } else {
                info("File does not exist: " . $filePath);
            }
            // Storage::delete($filePath);
            return response()->json(["message" => "Welcome video successfully deleted"], 200);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * Update the status of a welcome video.
     *
     * This function updates the status of a welcome video based on the provided video ID.
     * It attempts to find the WelcomeVideo record by the given ID and toggles its status
     * between "Active" and "Inactive". Upon successful update, it returns a JSON response
     * with a success message and the updated status of the video. If the video is not found
     * or an error occurs during the process, it returns a JSON response with a 400 status,
     * containing the appropriate error message.
     *
     * @param  int  $id The ID of the welcome video to update.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the success or failure of the status update.
     */

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
