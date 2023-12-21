<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\VideoCategory;
use Illuminate\Http\Request;

class VideoCategoryController extends Controller
{
    public function allVideoCategories()
    {
        try {
            $video_categories = VideoCategory::get();
            return response()->json(["video_categories" => $video_categories]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }


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
