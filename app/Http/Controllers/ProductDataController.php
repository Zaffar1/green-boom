<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductData;
use App\Models\ProductDataDimension;
use App\Models\ProductDataSize;
use App\Models\ProductDataTitle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductDataController extends Controller
{
    public function allProductData()
    {
        try {
            $all_product_data = ProductData::orderBy('id', 'DESC')->get();
            return response()->json(["all_product_data" => $all_product_data]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function addProductData(Request $request)
    {
        $validate = $request->validate([
            'product_id' => 'required',
            'sku_num' => 'required',
            'size' => 'required',
            'dimensions' => 'required',
            'Absorbency' => 'required',
            'qty' => 'required',
            'case' => 'required',
            'added_remediation_material' => 'required',
            'product_dimensions_size' => 'required',
            'product_dimensions_cm' => 'required',
            'packaging_dimensions_size' => 'required',
            'packaging_dimensions_cm' => 'required',
            'weight' => 'required',
            'product' => 'required',
        ]);

        try {
            $product = Product::find($request->product_id);

            if (!$product) {
                return response()->json(['message' => 'Invalid product']);
            } else {
                $validate['status'] = 'Active';
            }

            ProductData::create($validate);
            return response()->json(["message" => "Product Data successfully added"], 200);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function productData($id)
    {
        // $request->validate([
        //     'product_id' => 'required',
        // ]);
        try {
            $product = Product::find($id);
            if (!$product)
                return response()->json(["message" => "Invalid product"]);
            else
                // $productData = ProductData::whereProductId($product->id)->orderBy('id', 'DESC')->get();
                $productData = ProductData::whereProductId($product->id)->orderBy('id', 'DESC')->get();
            return response()->json(["data" => $productData]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }
}
