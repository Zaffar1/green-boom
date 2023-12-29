<?php

namespace App\Http\Controllers;

use App\Models\PerfectSale;
use App\Models\PerfectSaleMedia;
use Illuminate\Http\Request;

class PerfectSaleController extends Controller
{
    public function allPerfectSales()
    {
        try {
            $all_perfect_sales = PerfectSale::orderBy('id', 'DESC')->get();
            return response()->json(['all_data' => $all_perfect_sales]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function addPerfectSale(Request $request)
    {
        $validate = $request->validate([
            'title' => 'required'
        ]);
        try {
            $validate['status'] = 'Active';
            PerfectSale::create($validate);
            return response()->json(["message" => "PerfectSale successfully added"], 200);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }


    public function updatePerfectSale(Request $request)
    {
        $validate = $request->validate([
            'id' => 'required',
            'title' => 'required',
        ]);
        try {
            $perfect_sale = PerfectSale::find($request->id);
            if (!$perfect_sale)
                return response()->json(["message" => "Invalid perfect sale"]);
            $perfect_sale->update($validate);
            return response()->json(["message" => "Perfect Sale successfully updated"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }


    public function deletePerfectSale($id)
    {
        try {
            $perfect_sale = PerfectSale::find($id);
            if (!$perfect_sale)
                return response()->json(["message" => "Invalid perfect sale"]);
            $perfect_sale->delete();
            return response()->json(["message" => "Perfect Sale deleted"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }


    public function PerfectSaleStatus($id)
    {
        // $request->validate([
        //     "id" => "required",
        // ]);
        try {
            $perfect_sale = PerfectSale::find($id);
            if (!$perfect_sale)
                return response()->json(["message" => "Invalid perfect sale"]);
            if ($perfect_sale->status == "Active") {
                $perfect_sale->status = "InActive";
            } else {
                $perfect_sale->status = "Active";
            }
            $perfect_sale->save();
            return response()->json(["message" => "Perfect Sale status changed"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }


    public function perfectSaleMedia(Request $request, $id)
    {
        // $request->validate([
        //     'perfect_sale_id' => 'required',
        // ]);
        try {
            $perfect_sale = PerfectSale::find($id);
            if (!$perfect_sale)
                return response()->json(["message" => "Invalid perfect sale"]);
            else
                $perfectSaleMedia = PerfectSaleMedia::wherePerfectSaleId($id)->orderBy('id', 'DESC')->whereStatus('Active')->with('perfectSales')->get();
            return response()->json(["data" => $perfectSaleMedia]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }
}
