<?php

namespace App\Http\Controllers;

use App\Mail\SendOrderKitDetail;
use App\Models\OrderKitForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class OrderKitFormController extends Controller
{
    public function addOrderForm(Request $request)
    {
        try {
            $admin_email = "alizafar@mailinator.com";
            $data = new OrderKitForm([
                "order_kit_id" => $request->order_kit_id,
                "first_name" => $request->first_name,
                "last_name" => $request->last_name,
                "email" => $request->email,
                "phone" => $request->phone,
                "company" => $request->company,
                "country" => $request->country,
                "state" => $request->state,
                "zip_code" => $request->zip_code,
                "city" => $request->city,
                "address" => $request->address,
                // "status" => "Active",
            ]);
            $data->save();
            Mail::to($admin_email)->send(new SendOrderKitDetail($data));
            return response()->json(["message" => "Order kit successfully sent"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function orderKitData()
    {
        try {
            $orderKitForms = OrderKitForm::with('orderKit')->get();
            return response()->json(["kit_data" => $orderKitForms]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function deleteOrderKit($id)
    {
        try {
            $kit = OrderKitForm::find($id);
            if (!$kit) {
                return response()->json(["error" => "order kit not found"], 404);
            }
            $kit->delete();
            return response()->json(["message" => "Order kit data successfully deleted"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function viewOrderKit($id)
    {
        try {
            $kit = OrderKitForm::whereOrderKitId($id)->get();
            if (!$kit) {
                return response()->json(["error" => "order kit not found"], 404);
            }
            return response()->json(["kit_detail" => $kit]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }
}
