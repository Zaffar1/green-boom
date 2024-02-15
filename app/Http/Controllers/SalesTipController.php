<?php

namespace App\Http\Controllers;

use App\Models\SalesTip;
use Illuminate\Http\Request;

class SalesTipController extends Controller
{
    public function allSalesTips()
    {
        try {
            $sales_tips = SalesTip::orderBy('id', 'DESC')->get();
            return response()->json(["sales_tips_list" => $sales_tips]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    ////////////// For Customer
    public function customerSalesTips()
    {
        try {
            $sales_tips = SalesTip::whereStatus('Active')->orderBy('id', 'DESC')->get();
            return response()->json(["sales_tips_list" => $sales_tips]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    ///////////////// End 

    public function addSalesTip(Request $request)
    {

        $validate = $request->validate([
            'title' => 'required',
            'description' => 'required',
            'file' => 'required',
        ]);
        try {
            $validate['status'] = 'Active';
            $new_name = time() . '.' . $request->file->extension();
            $path = $request->file('file')->storeAs('salesTips', $new_name, 's3');
            $validate['file'] = $path;
            // $request->file->move(public_path('storage/salesTips'), $new_name);
            // $validate['file'] = "storage/salesTips/$new_name";
            SalesTip::create($validate);
            return response()->json(["message" => "SalesTip successfully added"], 200);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }


    public function updateSalesTip(Request $request)
    {
        try {
            $sales_tip = SalesTip::find($request->id);
            if (!$sales_tip) {
                return response()->json(["error" => "SalesTip data not found"], 404);
            }
            $sales_tip->title = $request->title;
            $sales_tip->description = $request->description;
            if ($request->hasFile('file')) {

                // Use unlink for direct file deletion
                if (file_exists($sales_tip->file)) {
                    unlink($sales_tip->file);
                }
                $new_name = time() . '.' . $request->file->extension();
                $path = $request->file('file')->storeAs('salesTips', $new_name, 's3');
                $sales_tip->file = $path;
                // $request->file->move(public_path('storage/salesTips'), $new_name);
                // $sales_tip->file = "storage/salesTips/$new_name";
            }
            $sales_tip->save();
            return response()->json(["message" => "SalesTip successfully updated"], 200);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function deleteSalesTip($id)
    {
        try {
            $sales_tip = SalesTip::find($id);
            if (!$sales_tip)
                return response()->json(["message" => "Invalid SalesTip"]);
            else
                $sales_tip->delete();
            // Use unlink for direct file deletion
            if (file_exists($sales_tip->file)) {
                unlink($sales_tip->file);
            }
            // Storage::delete($sales_tip->file);
            return response()->json(["message" => "Sales_tip successfully deleted"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }


    public function salesTipStatus($id)
    {
        try {
            $sales_tip = SalesTip::find($id);
            if (!$sales_tip)
                return response()->json(["message" => "Invalid salesTip"]);
            if ($sales_tip->status == "Active") {
                $sales_tip->status = "InActive";
            } else {
                $sales_tip->status = "Active";
            }
            $sales_tip->save();
            return response()->json(["message" => "salesTip status changed"], 200);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }
}
