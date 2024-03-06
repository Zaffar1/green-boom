<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\VideoCategory;
use Illuminate\Http\Request;

class VideoCategoryController extends Controller
{
    /**
     * Retrieve all video categories.
     *
     * This function retrieves all video categories from the database.
     * If successful, it returns a JSON response containing the retrieved video categories.
     * If any error occurs during the process, it catches the exception and returns a JSON response with a 400 status containing the error message.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing the retrieved video categories.
     */

    public function allVideoCategories()
    {
        try {
            $video_categories = VideoCategory::get();
            return response()->json(["video_categories" => $video_categories]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * Add a new video category.
     *
     * This function adds a new video category to the database based on the provided request data.
     * The request data must include the title and description of the video category.
     * It validates the request data to ensure that the title and description fields are required.
     * If successful, it returns a JSON response indicating that the video category has been successfully added.
     * If any error occurs during the process, it catches the exception and returns a JSON response with a 400 status containing the error message.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing the data for the new video category.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the result of the video category addition.
     */


    public function addVideoCat(Request $request)
    {
        try {
            $validate = $request->validate([
                'title' => 'required',
                'description' => 'required',
                // 'image' => 'required|mimes:png,jpg,jpeg',
            ]);

            $validate['status'] = 'Active';
            $new_name = time() . '.' . $request->image->extension();
            $request->image->move(public_path('storage/videoCategory'), $new_name);
            $validate['image'] = "storage/videoCategory/$new_name";
            VideoCategory::create($validate);
            return response()->json(['message' => 'Video category successfully added']);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * Update a video category.
     *
     * This function updates an existing video category in the database based on the provided request data.
     * The request data must include the ID of the video category to be updated, as well as the updated title and description.
     * It validates the request data to ensure that the ID, title, and description fields are required.
     * If an image file is included in the request, it updates the image file of the video category.
     * If successful, it returns a JSON response indicating that the video category has been successfully updated.
     * If any error occurs during the process, it catches the exception and returns a JSON response with a 400 status containing the error message.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing the data for updating the video category.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the result of the video category update operation.
     */

    public function updateVideoCat(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'title' => 'required',
            'description' => 'required',
            // 'image'=>'required'
        ]);
        try {
            $video_cat = VideoCategory::find($request->id);
            $video_cat->title = $request->title;
            $video_cat->description = $request->description;

            if ($request->hasFile('image')) {

                if (file_exists($video_cat->image)) {
                    unlink($video_cat->image);
                }

                $new_name = time() . '.' . $request->image->extension();
                $request->image->move(public_path('storage/videoCategory'), $new_name);
                $video_cat->image = "storage/videoCategory/$new_name";
            }
            $video_cat->save();
            return response()->json(['message' => 'Video category successfully updated']);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * Delete a video category.
     *
     * This function deletes a video category from the database based on the provided ID.
     * It attempts to find the video category with the given ID and deletes it.
     * If successful, it returns a JSON response indicating that the video category has been successfully deleted.
     * If the video category does not exist, it returns a JSON response with a message indicating the invalid ID.
     * If any error occurs during the process, it catches the exception and returns a JSON response with a 400 status containing the error message.
     *
     * @param int $id The ID of the video category to be deleted.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the result of the video category deletion operation.
     */

    public function deleteVideoCategory($id)
    {
        try {
            $video_cat = VideoCategory::find($id);
            if (!$video_cat)
                return response()->json(["message" => "Invalid id"]);
            else
                $video_cat->delete();
            // Use unlink for direct file deletion
            if (file_exists($video_cat->image)) {
                unlink($video_cat->image);
            }
            // Storage::delete($video_cat->image);
            return response()->json(["message" => "Video category successfully deleted"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * Change the status of a video category.
     *
     * This function changes the status of a video category (Active/Inactive) based on the provided ID.
     * It attempts to find the video category with the given ID and updates its status accordingly.
     * If successful, it returns a JSON response indicating that the video category status has been changed.
     * If the video category does not exist, it returns a JSON response with a message indicating the invalid ID.
     * If any error occurs during the process, it catches the exception and returns a JSON response with a 400 status containing the error message.
     *
     * @param int $id The ID of the video category for which the status needs to be changed.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the result of the video category status change operation.
     */

    public function videoCatStatus($id)
    {
        try {
            $video = VideoCategory::find($id);
            if (!$video)
                return response()->json(["message" => "Invalid video category"]);
            if ($video->status == "Active") {
                $video->status = "InActive";
            } else {
                $video->status = "Active";
            }
            $video->save();
            return response()->json(["message" => "Video category status changed"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * Retrieve videos belonging to a video category.
     *
     * This function retrieves videos associated with a specific video category based on the provided ID.
     * It attempts to find the video category with the given ID and fetches the videos belonging to it.
     * If successful, it returns a JSON response containing the list of videos associated with the video category.
     * If the video category does not exist, it returns a JSON response with a message indicating the invalid ID.
     * If any error occurs during the process, it catches the exception and returns a JSON response with a 400 status containing the error message.
     *
     * @param \Illuminate\Http\Request $request The HTTP request instance.
     * @param int $id The ID of the video category for which to retrieve videos.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the list of videos associated with the video category.
     */

    public function videoCatVideos(Request $request, $id)
    {
        // $request->validate([
        //     'video_cat_id' => 'required',
        // ]);
        try {
            $video = VideoCategory::find($id);
            if (!$video)
                return response()->json(["message" => "Invalid video category"]);
            else
                $videos = Video::whereVideoCatId($id)->orderBy('id', 'DESC')->get();
            return response()->json(["videos" => $videos]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }
}
