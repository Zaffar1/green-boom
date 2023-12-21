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
    public function allVideos()
    {
        try {
            $videos = Video::orderBy('id', 'DESC')->get();
            return response()->json(["all_videos" => $videos]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function customerAllVideos()
    {
        try {
            $videos = Video::whereStatus('Active')->orderBy('id', 'DESC')->get();
            return response()->json(["all_videos" => $videos]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

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

    public function addVideo(Request $request)
    {
        $validate = $request->validate([
            'video_cat_id' => 'required',
            'title' => 'required',
            'description' => 'required',
            'file' => 'required|mimes:mp4,mov,avi|max:204800', // Adjust the max size as needed
        ]);
        try {
            $validate['status'] = 'Active';
            // $validate['file'] = $request->file('file')->store('public/videos');
            $new_name = time() . '.' . $request->file->extension();
            $request->file->move(public_path('storage/videos'), $new_name);
            $path = "storage/videos/$new_name";
            $validate['file'] = $path;
            Video::create($validate);
            return response()->json(["message" => "Video successfully added"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

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
                $request->file->move(public_path('storage/videos'), $new_name);
                $path = "storage/videos/$new_name";
                $video->file = $path;
            }
            $video->save();
            return response()->json(["message" => "Video successfully updated"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

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