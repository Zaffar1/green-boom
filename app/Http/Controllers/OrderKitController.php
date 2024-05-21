<?php

namespace App\Http\Controllers;

use App\Models\OrderKit;
use Illuminate\Http\Request;

class OrderKitController extends Controller
{
    /**
     * The function `allOrderKits` retrieves all order kits in descending order of their IDs and
     * returns them as a JSON response, handling any errors that may occur.
     * 
     * @return The `allOrderKits` function is returning a JSON response. If the retrieval of `OrderKit`
     * records is successful, it returns a JSON response containing the fetched order kits in the
     * "order_kit" key. If an error occurs during the retrieval process, it returns a JSON response
     * with an "error" key containing the error message.
     */
    public function allOrderKits()
    {
        try {
            $order_kits = OrderKit::orderBy('id', 'DESC')->get();
            return response()->json(["order_kit" => $order_kits]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * This PHP function retrieves all active order kits and returns them as a JSON response.
     * 
     * @return The function `customerAllOrderKits()` is returning a JSON response. If the query is
     * successful, it returns a JSON response containing an array with the key "order_kit_list" and the
     * value of the retrieved order kits. If there is an error during the execution of the query, it
     * returns a JSON response with an error message under the key "error".
     */
    public function customerAllOrderKits()
    {
        try {
            $order_kits = OrderKit::whereStatus('Active')->orderBy('id', 'DESC')->get();
            return response()->json(["order_kit_list" => $order_kits]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * The function `addOrderKit` in PHP validates and adds an order kit with an image and file to the
     * database.
     * 
     * @param Request request The `addOrderKit` function is used to add a new order kit based on the
     * data provided in the request. Let's break down the process happening in this function:
     * 
     * @return The function `addOrderKit` is returning a JSON response. If the order kit is
     * successfully added, it returns a JSON response with a success message "Order kit successfully
     * added". If there is an error during the process, it returns a JSON response with an error
     * message containing the exception message.
     */
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
            // $file->move(public_path('storage/orderKit'), $new_name);
            // $validate['image'] = "storage/orderKit/$new_name";
            $path = $request->file('image')->storeAs('orderKit', $new_name, 's3');
            $validate['image'] = $path;
            $new_name = time() . '.' . $request->file->extension();
            $request->file->move(public_path('storage/orderKit/videos'), $new_name);
            $path = "storage/orderKit/videos/$new_name";
            $validate['file'] = $path;
            OrderKit::create($validate);
            return response()->json(["message" => "Order kit successfully added"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }
}
