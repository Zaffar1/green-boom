<?php

namespace App\Http\Controllers;

use App\Models\OrderKitForm;
use Illuminate\Http\Request;

class OrderKitFormController extends Controller
{
    public function addOrderForm(Request $request)
    {
        try {
            $data = new OrderKitForm([
                "first_name" => $request->first_name,
                "last_name" => $request->last_name,
                "email" => $request->email,
                "phone" => $request->phone,
                "company" => $request->company,
                "country" => $request->country,
                "state" => $request->state,
                "zip_code" => $request->zip_code,
                "address" => $request->address,
                "status" => "Active",
            ]);
            $data->save();
            return response()->json(["message" => "Order kit successfully sent"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }
}
