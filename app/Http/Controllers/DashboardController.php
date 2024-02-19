<?php

namespace App\Http\Controllers;

use App\Models\OrderKitForm;
use App\Models\Product;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard()
    {
        try {
            $products = Product::count();
            $order_kit = OrderKitForm::count();
            return response()->json(["products" => $products, "order_kit" => $order_kit]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }
}
