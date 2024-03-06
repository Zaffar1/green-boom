<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Google_Client;
use Google_Service_YouTube;
use Google_Service_YouTube_SearchListResponse;

class VideoController extends Controller
{
    /**
     * Retrieve all videos.
     *
     * This function retrieves all videos available in the system.
     * It fetches videos ordered by their IDs in descending order.
     * If successful, it returns a JSON response containing the list of all videos.
     * If any error occurs during the process, it catches the exception and returns a JSON response with a 400 status containing the error message.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing the list of all videos.
     */
    public function allVideos()
    {
        try {
            $videos = Video::orderBy('id', 'DESC')->get();
            return response()->json(["all_videos" => $videos]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * Retrieve all active videos for customers.
     *
     * This function retrieves all active videos available for customers in the system.
     * It fetches videos ordered by their IDs in descending order and filtered by 'Active' status.
     * If successful, it returns a JSON response containing the list of all active videos for customers.
     * If any error occurs during the process, it catches the exception and returns a JSON response with a 400 status containing the error message.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing the list of all active videos for customers.
     */

    public function customerAllVideos()
    {
        try {
            $videos = Video::whereStatus('Active')->orderBy('id', 'DESC')->get();
            return response()->json(["all_videos" => $videos]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * Retrieve details of a specific video.
     *
     * This function retrieves the details of a specific video based on the provided video ID.
     * It validates the request parameters to ensure the 'video_id' is provided.
     * If the video is found, it returns a JSON response containing the details of the video.
     * If the video is not found or any error occurs during the process, it catches the exception
     * and returns a JSON response with a 400 status containing the error message.
     *
     * @param  \Illuminate\Http\Request  $request The HTTP request containing the 'video_id' parameter.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the details of the specified video.
     */

    public function videoDetail(Request $request)
    {
        $request->validate([
            'video_id' => 'required',
        ]);
        try {
            $video = Video::find($request->video_id);
            return response()->json(['video_detail' => $video]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * Add a new video.
     *
     * This function adds a new video to the system.
     * It validates the request parameters to ensure the required fields are provided.
     * If validation passes, the function sets the status of the video to 'Active', uploads
     * the video file and its thumbnail to the cloud storage (Amazon S3), and creates a new
     * record in the 'videos' table with the provided details.
     * If any error occurs during the process, it catches the exception and returns a JSON
     * response with a 400 status containing the error message.
     *
     * @param  \Illuminate\Http\Request  $request The HTTP request containing the video details.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the success or failure of the operation.
     */


    public function addVideo(Request $request)
    {
        $validate = $request->validate([
            // 'video_cat_id' => 'required',
            'title' => 'required',
            'description' => 'required',
            'file' => 'required|mimes:mp4,mov,avi', // Adjust the max size as needed
        ]);
        try {
            $validate['status'] = 'Active';
            // $validate['file'] = $request->file('file')->store('public/videos');
            $new_name = time() . '.' . $request->file->extension();
            $path = $request->file('file')->storeAs('videos', $new_name, 's3');
            // $request->file->move(public_path('storage/videos'), $new_name);
            // $path = "storage/videos/$new_name";
            $validate['file'] = $path;
            $new_name = time() . '.' . $request->thumbnail->extension();
            $thum_path = $request->file('thumbnail')->storeAs('videos/thumbnail', $new_name, 's3');
            $validate['thumbnail'] = $thum_path;
            // $request->thumbnail->move(public_path('storage/videos/thumbnail'), $new_name);
            // $validate['thumbnail'] = "storage/videos/thumbnail/$new_name";
            Video::create($validate);
            return response()->json(["message" => "Video successfully added"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * Update an existing video.
     *
     * This function updates the details of an existing video in the system.
     * It retrieves the video record by its ID and validates the request parameters.
     * If validation passes, it updates the title and description of the video.
     * If the request contains a new video file or thumbnail, it deletes the existing files
     * from the cloud storage (Amazon S3), uploads the new files, and updates the file paths
     * in the database accordingly.
     * Finally, it saves the changes to the database and returns a JSON response indicating
     * the success of the operation.
     * If any error occurs during the process, it catches the exception and returns a JSON
     * response with a 400 status containing the error message.
     *
     * @param  \Illuminate\Http\Request  $request The HTTP request containing the updated video details.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the success or failure of the operation.
     */

    public function updateVideo(Request $request)
    {
        // $validate = $request->validate([
        //     "id" => "required",
        //     "title" => "required",
        //     "description" => "required",
        //     "file" => "required|mimes:mp4,mov,avi|max:204800"
        // ]);

        try {
            $video = Video::find($request->id);
            if (!$video)
                return response()->json(["message" => "Invalid id"]);
            $video->title = $request->title;
            $video->description = $request->description;
            if ($request->hasFile('file')) {
                // Storage::delete($video->file);
                // Use unlink for direct file deletion
                if (file_exists($video->file)) {
                    unlink($video->file);
                }
                // $video->file = $request->file('file')->store('public/videos');
                $new_name = time() . '.' . $request->file->extension();
                $path = $request->file('file')->storeAs('videos', $new_name, 's3');
                // $request->file->move(public_path('storage/videos'), $new_name);
                // $path = "storage/videos/$new_name";
                $video->file = $path;
            }
            if ($request->hasFile('thumbnail')) {
                $new_name = time() . '.' . $request->thumbnail->extension();
                $thum_path = $request->file('thumbnail')->storeAs('videos/thumbnail', $new_name, 's3');
                $video->thumbnail = $thum_path;
                // $request->thumbnail->move(public_path('storage/videos/thumbnail'), $new_name);
                // $video->thumbnail = "storage/videos/thumbnail/$new_name";
            }
            $video->save();
            return response()->json(["message" => "Video successfully updated"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * Delete a video.
     *
     * This function deletes a video from the system.
     * It first attempts to find the video record by its ID.
     * If the video is not found, it returns a JSON response with a 404 status indicating
     * that the video was not found.
     * If the video is found, it deletes the video record from the database and attempts
     * to delete the associated video file from the cloud storage (Amazon S3).
     * Finally, it returns a JSON response indicating the success of the operation.
     * If any error occurs during the process, it catches the exception and returns a JSON
     * response with a 400 status containing the error message.
     *
     * @param  int  $id The ID of the video to be deleted.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the success or failure of the operation.
     */

    public function deleteVideo($id)
    {
        try {
            $video = Video::find($id);
            if (!$video) {
                return response()->json(["error" => "Video not found"], 404);
            }
            $filePath = $video->file;
            $video->delete();
            // Storage::delete($filePath);
            // Use unlink for direct file deletion
            if (file_exists($filePath)) {
                unlink($video->file);
            }
            return response()->json(["message" => "Video successfully deleted"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }


    public function youTubeVideos()
    {

        $client = new Google_Client();
        $client->setDeveloperKey(config('services.youtube.api_key'));

        $youtube = new Google_Service_YouTube($client);

        $channelId = 'UC0nfyUEzygyuPrpOFERZm-Q'; // Replace with the target channel ID

        $params = [
            'type' => 'video',
            'q' => '', // You can add a search query here if needed
            'channelId' => $channelId,
            'maxResults' => 10,
        ];

        /** @var Google_Service_YouTube_SearchListResponse $videos */
        $videos = $youtube->search->listSearch('id,snippet', $params);

        // Extract video URLs
        $videoUrls = [];
        foreach ($videos->getItems() as $video) {
            $videoId = $video->getId()->getVideoId();
            $videoUrl = "https://www.youtube.com/watch?v=$videoId";
            $videoUrls[] = $videoUrl;
        }

        // Convert the $videos object to an array
        $videosArray = json_decode(json_encode($videos), true);

        // Add video URLs to the response array
        $videosArray['video_urls'] = $videoUrls;

        // Return the videos with video URLs as a JSON response
        return response()->json(['all_videos' => $videosArray]);
    }

    /**
     * Change the status of a video.
     *
     * This function changes the status of a video by toggling between "Active" and "Inactive".
     * It first attempts to find the video record by its ID.
     * If the video is not found, it returns a JSON response with a 404 status indicating
     * that the video was not found.
     * If the video is found, it toggles the status between "Active" and "Inactive" and saves
     * the changes to the database.
     * Finally, it returns a JSON response indicating the success of the operation.
     * If any error occurs during the process, it catches the exception and returns a JSON
     * response with a 400 status containing the error message.
     *
     * @param  int  $id The ID of the video for which the status needs to be changed.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the success or failure of the operation.
     */

    public function videoStatus($id)
    {
        try {
            $video = Video::find($id);
            if (!$video)
                return response()->json(["message" => "Invalid video"]);
            if ($video->status == "Active") {
                $video->status = "InActive";
            } else {
                $video->status = "Active";
            }
            $video->save();
            return response()->json(["message" => "Video status changed"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }
}


// $client = new Google_Client();
// $client->setDeveloperKey(config('services.youtube.api_key'));

// $youtube = new Google_Service_YouTube($client);

// $channelId = 'UC0nfyUEzygyuPrpOFERZm-Q'; // Replace with the target channel ID

// $params = [
//     'type' => 'video',
//     'q' => '', // You can add a search query here if needed
//     'channelId' => $channelId,
//     'maxResults' => 10,
// ];

// /** @var Google_Service_YouTube_SearchListResponse $videos */
// $videos = $youtube->search->listSearch('id,snippet', $params);

// // Convert the $videos object to an array
// $videosArray = json_decode(json_encode($videos), true);

// // Return the videos as JSON response
// return response()->json(['all_videos' => $videosArray]);