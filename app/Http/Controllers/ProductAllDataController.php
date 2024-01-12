<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductDataDimension;
use App\Models\ProductDataSize;
use App\Models\ProductDataTitle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
                $product_data_small = ["size" => $product_small];

                // Small size dimension
                $product_small_dimension = ProductDataDimension::whereProductDataSizeId($product_small->id)->first();
                if ($product_small_dimension) {
                    $product_data_small = ["dimension" => $product_small_dimension];
                }

                // Small size title_sku
                $product_data_title_sku = ProductDataTitle::whereProductDataSizeId($product_small->id)->first();
                if ($product_data_title_sku) {
                    $product_data_small = ["title" => $product_data_title_sku];
                }

                // Add small size to sizePickerArray
                $sizePickerArray = (object)["id" => "small", "title" => "small"];
            }

            // Medium size
            $product_medium = ProductDataSize::whereProductId($id)->whereSize('medium')->first();
            if ($product_medium) {
                $product_data_medium = ["size" => $product_medium];

                // Medium size dimension
                $product_medium_dimension = ProductDataDimension::whereProductDataSizeId($product_medium->id)->first();
                if ($product_medium_dimension) {
                    $product_data_medium = ["dimension" => $product_medium_dimension];
                }

                // Medium size title_sku
                $product_data_title_sku = ProductDataTitle::whereProductDataSizeId($product_medium->id)->first();
                if ($product_data_title_sku) {
                    $product_data_medium = ["title" => $product_data_title_sku];
                }

                // Add medium size to sizePickerArray
                $sizePickerArray[] = (object)["id" => "medium", "title" => "medium"];
            }

            // Large size
            $product_large = ProductDataSize::whereProductId($id)->whereSize('large')->first();
            if ($product_large) {
                $product_data_large = ["size" => $product_large];

                // Large size dimension
                $product_large_dimension = ProductDataDimension::whereProductDataSizeId($product_large->id)->first();
                if ($product_large_dimension) {
                    $product_data_large = ["dimension" => $product_large_dimension];
                }

                // Large size title_sku
                $product_data_title_sku = ProductDataTitle::whereProductDataSizeId($product_large->id)->first();
                if ($product_data_title_sku) {
                    $product_data_large = ["title" => $product_data_title_sku];
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

        // if ($request->case_data == "absorbency_bag") {
        $validate['absorbency_bag'] = $request->absorbency_bag;
        // }

        // if ($request->case_data == "absorbency_drum") {
        $validate['absorbency_drum'] = $request->absorbency_drum;
        // }

        try {
            $product = Product::find($request->product_id);

            if (!$product) {
                return response()->json(['message' => 'Invalid product']);
            } else {
                $validate['status'] = 'Active';
            }

            $productDataSize = ProductDataSize::create($validate);

            $validate2 = [
                'product_id' => $request->product_id,
                'product_dimensions(LHW)1' => $request->input('product_dimensions(LHW)1'),
                'product_dimensions(LHW)2' => $request->input('product_dimensions(LHW)2'),
                'packaging_dimensions(LHW)1' => $request->input('packaging_dimensions(LHW)1'),
                'packaging_dimensions(LHW)2' => $request->input('packaging_dimensions(LHW)2'),
                'weight_product' => $request->input('weight_product'),
                'total_weight_product' => $request->input('total_weight_product'),
                "product_data_size_id" => $productDataSize->id,
            ];

            $validation2 = Validator::make($validate2, [
                'product_id' => 'required',
                'product_dimensions(LHW)1' => 'required',
                'product_dimensions(LHW)2' => 'required',
                'packaging_dimensions(LHW)1' => 'required',
                'packaging_dimensions(LHW)2' => 'required',
                'weight_product' => 'required',
                'total_weight_product' => 'required',
                'product_data_size_id' => 'required',
            ]);

            if ($validation2->fails()) {
                return response()->json(["error" => $validation2->errors()], 400);
            }

            ProductDataDimension::create($validate2);

            $validate3 = [
                "product_id" => $request->product_id,
                "title_remediation" => $request->title_remdiation,
                "sku_rem" => $request->sku_rem,
                "product_data_size_id" => $productDataSize->id,
            ];

            $validation3 = Validator::make($validate3, [
                "product_id" => 'required',
                "sku_rem" => 'required',
                "title_remediation" => 'required',
                "product_data_size_id" => 'required',
            ]);

            if ($validation3->fails()) {
                return response()->json(["error" => $validation3->errors()], 400);
            }

            ProductDataTitle::create($validate3);

            return response()->json(["message" => "Product Data successfully added"], 200);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }
}
