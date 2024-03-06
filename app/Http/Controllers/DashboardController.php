<?php

namespace App\Http\Controllers;

use App\Models\OrderKitForm;
use App\Models\Product;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * The `dashboard` function retrieves the count of products and order kit forms and returns them as
     * JSON, handling any errors with an appropriate response.
     * 
     * @return The `dashboard()` function is returning a JSON response with the counts of products and
     * order kit forms. If there are no errors, it will return a JSON response with the counts. If an
     * error occurs during the execution of the function, it will return a JSON response with the error
     * message.
     */
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
