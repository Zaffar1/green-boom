<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductDataDimension;
use App\Models\ProductDataSize;
use App\Models\ProductDataTitle;
use Illuminate\Http\Request;

class ProductAllDataController extends Controller
{
    public function allProductData($id)
    {
        try {
            $product_data = Product::find($id);

            if (!$product_data) {
                return response()->json(["message" => "Invalid product"]);
            }

            $product_data_small = [];
            $product_data_medium = [];
            $product_data_large = [];
            $sizePickerArray = [];

            // Small size
            $product_small = ProductDataSize::whereProductId($id)->whereSize('small')->first();
            if ($product_small) {
                $product_data_small[] = ["size" => $product_small];

                // Small size dimension
                $product_small_dimension = ProductDataDimension::whereProductDataSizeId($product_small->id)->first();
                if ($product_small_dimension) {
                    $product_data_small[] = ["dimension" => (object)$product_small_dimension];
                }

                // Small size title_sku
                $product_data_title_sku = ProductDataTitle::whereProductDataSizeId($product_small->id)->first();
                if ($product_data_title_sku) {
                    $product_data_small[] = ["title" => $product_data_title_sku];
                }

                // Add small size to sizePickerArray
                $sizePickerArray[] = (object)["id" => "small", "title" => "small"];
            }

            // Medium size
            $product_medium = ProductDataSize::whereProductId($id)->whereSize('medium')->first();
            if ($product_medium) {
                $product_data_medium[] = ["size" => $product_medium];

                // Medium size dimension
                $product_medium_dimension = ProductDataDimension::whereProductDataSizeId($product_medium->id)->first();
                if ($product_medium_dimension) {
                    $product_data_medium[] = ["dimension" => (object)$product_medium_dimension];
                }

                // Medium size title_sku
                $product_data_title_sku = ProductDataTitle::whereProductDataSizeId($product_medium->id)->first();
                if ($product_data_title_sku) {
                    $product_data_medium[] = ["title" => $product_data_title_sku];
                }

                // Add medium size to sizePickerArray
                $sizePickerArray[] = (object)["id" => "medium", "title" => "medium"];
            }

            // Large size
            $product_large = ProductDataSize::whereProductId($id)->whereSize('large')->first();
            if ($product_large) {
                $product_data_large[] = ["size" => $product_large];

                // Large size dimension
                $product_large_dimension = ProductDataDimension::whereProductDataSizeId($product_large->id)->first();
                if ($product_large_dimension) {
                    $product_data_large[] = ["dimension" => (object)$product_large_dimension];
                }

                // Large size title_sku
                $product_data_title_sku = ProductDataTitle::whereProductDataSizeId($product_large->id)->first();
                if ($product_data_title_sku) {
                    $product_data_large[] = ["title" => $product_data_title_sku];
                }

                // Add large size to sizePickerArray
                $sizePickerArray[] = (object)["id" => "large", "title" => "large"];
            }

            return response()->json([
                "product_data" => $product_data,
                "small" => $product_data_small,
                "medium" => $product_data_medium,
                "large" => $product_data_large,
                "sizePicker" => $sizePickerArray
            ]);
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
            'qty_case' => 'required',
            'added_remediation_material' => 'required',
        ]);

        $validate2 = $request->validate([
            'product_id' => 'required',
            'product_dimensions(LHW)1' => 'required',
            'product_dimensions(LHW)2' => 'required',
            'packaging_dimensions(LHW)1' => 'required',
            'packaging_dimensions(LHW)2' => 'required',
            'weight_product' => 'required',
            'total_weight_product' => 'required',
        ]);

        if ($request->case_data == "absorbency_bag") {
            $validate['absorbency_bag'] = $request->case_data;
        }

        if ($request->case_data == "absorbency_drum") {
            $validate['absorbency_drum'] = $request->case_data;
        }

        try {
            $product = Product::find($request->product_id);

            if (!$product) {
                return response()->json(['message' => 'Invalid product']);
            } else {
                $validate['status'] = 'Active';
            }

            $productDataSize = ProductDataSize::create($validate);

            // Remove the unnecessary field
            // unset($validate2['product_data_size']);

            // Add the product_data_size_id
            $validate2['product_data_size_id'] = $productDataSize->id;

            ProductDataDimension::create($validate2);

            $validate3 = $request->validate([
                "product_id" => $request->product_id,
                "title_remediation" => $request->title,
                "sku_rem" => $request->sku_num,
                "product_data_size_id" => $productDataSize->id,
            ]);

            ProductDataTitle::create($validate3);

            return response()->json(["message" => "Product Data successfully added"], 200);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }
}
