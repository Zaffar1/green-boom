<?php

namespace App\Http\Controllers;

use App\Models\OrderKitVideo;
use Illuminate\Http\Request;

class OrderKitVideoController extends Controller
{
    public function allOrderKitVideos()
    {
        try {
            $order_kit_videos = OrderKitVideo::orderBy('id', 'DESC')->get();
            return response()->json(["order_kit_videos" => $order_kit_videos]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }


    public function customerAllOrderKitVideos()
    {
        try {
            $order_kit_videos = OrderKitVideo::whereStatus('Active')->orderBy('id', 'DESC')->get();
            return response()->json(["order_kit_videos" => $order_kit_videos]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }


    public function addOrderKitVideo(Request $request)
    {
        $validate = $request->validate([
            "file" => "required",
        ]);
        try {
            $validate['title'] = 'Active';
            $validate['description'] = 'Active';
            $validate['status'] = 'Active';
            $file = $request->file('file');
            $new_name = time() . '.' . $file->extension();
            $file->move(public_path('storage/orderKit/videos'), $new_name);
            $validate['file'] = "storage/orderKit/videos/$new_name";
            OrderKitVideo::create($validate);
            return response()->json(["message" => "Order kit video successfully added"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }
}
