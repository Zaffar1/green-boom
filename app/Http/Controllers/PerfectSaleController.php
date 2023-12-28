<?php

namespace App\Http\Controllers;

use App\Models\PerfectSale;
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
}
