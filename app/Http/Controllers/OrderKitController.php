<?php

namespace App\Http\Controllers;

use App\Models\OrderKit;
use Illuminate\Http\Request;

class OrderKitController extends Controller
{
    public function allOrderKits()
    {
        try {
            $order_kits = OrderKit::with('videos')->orderBy('id', 'DESC')->get();
            return response()->json(["order_kit" => $order_kits]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function customerAllOrderKits()
    {
        try {
            $order_kits = OrderKit::whereStatus('Active')->with('videos')->orderBy('id', 'DESC')->get();
            return response()->json(["order_kit_list" => $order_kits]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function addOrderKit(Request $request)
    {
        $validate = $request->validate([
            "title" => "required",
            "short_description" => "required",
            "description" => "required",
            "kit_includes" => "required",
        ]);
        $validate['status'] = 'Active';
        try {
            $file = $request->file('image');
            $new_name = time() . '.' . $file->extension();
            $file->move(public_path('storage/orderKit'), $new_name);
            $validate['image'] = "storage/orderKit/$new_name";
            OrderKit::create($validate);
            return response()->json(["message" => "Order kit successfully added"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }
}
