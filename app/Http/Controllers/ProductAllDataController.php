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
            if (!$product_data)
                return response()->json(["message" => "Invalid product"]);
            else
                return response()->json(["products" => $product_data]);
            $product_data_small = [];
            // $product_data_small_dimension = [];
            $product_data_medium = [];
            // $product_data_medium_dimension = [];
            $product_data_large = [];
            // $product_data = [];
            // $product_data_large_dimension = [];
            // $product_data_title = [];


            // $product_title = ProductDataTitle::whereProductId($id)->first();
            // if ($product_title) {
            //     array_push($product_data_small, $product_title);
            // }
            $product_small = ProductDataSize::whereProductId($product_data->id)->whereSize('small')->first();
            if ($product_small) {
                array_push($product_data_small, $product_small);
                return response()->json(["product_small" => $product_small]);
            }
            $product_small_dimension = ProductDataDimension::whereProductDataSizeId($product_small->id)->first();
            if ($product_small_dimension) {
                array_push($product_data_small, $product_small_dimension);
                return response()->json(["product_small_dimension" => $product_small_dimension]);
            } else {
                $product_small_dimension = [];
            }

            $product_data_title_sku = ProductDataTitle::whereProductDataSizeId($product_small->id)->first();
            if ($product_data_title_sku) {
                array_push($product_data_small, $product_data_title_sku);
                return response()->json(["product_data_title_sku" => $product_data_title_sku]);
            } else {
                $product_data_title_sku = [];
            }

            $product_medium = ProductDataSize::whereProductId($product_data->id)->whereSize('medium')->first();
            if ($product_medium) {
                array_push($product_data_medium, $product_medium);
                return response()->json(["product_medium" => $product_medium]);
            } else {
                $product_medium = [];
            }

            $product_medium_dimension = ProductDataDimension::whereProductDataSizeId($product_medium->id)->first();
            if ($product_medium_dimension) {
                array_push($product_data_medium, $product_medium_dimension);
            } else {
                $product_medium_dimension = [];
            }

            $product_data_title_sku = ProductDataTitle::whereProductDataSizeId($product_medium->id)->first();
            if ($product_data_title_sku) {
                array_push($product_data_large, $product_data_title_sku);
            } else {
                $product_data_title_sku = [];
            }


            $product_large = ProductDataSize::whereProductId($product_data->id)->whereSize('large')->first();
            if ($product_large) {
                array_push($product_data_large, $product_large);
            } else {
                $product_large = [];
            }

            $product_large_dimension = ProductDataDimension::whereProductDataSizeId($product_large->id)->first();
            if ($product_large_dimension) {
                array_push($product_data_small, $product_large_dimension);
            } else {
                $product_large_dimension = [];
            }

            $product_data_title_sku = ProductDataTitle::whereProductDataSizeId($product_large->id)->first();
            if ($product_data_title_sku) {
                array_push($product_data_large, $product_data_title_sku);
            } else {
                $product_data_title_sku = [];
            }

            if (!$product_small) {
                $product_small = [];
            } else
                $sizePickerArray = [];

            if ($product_small->isEmpty()) {
                $product_small = [];
            } else {
                $size = [
                    "id" => "small",
                    "title" => "small"
                ];
                array_push($sizePickerArray, $size);
            }

            if ($product_medium->isEmpty()) {
                $product_medium = [];
            } else {
                $size = [
                    "id" => "medium",
                    "title" => "medium"
                ];
                array_push($sizePickerArray, $size);
            }

            if ($product_large->isEmpty()) {
                $product_large = [];
            } else {
                $size = [
                    "id" => "large",
                    "title" => "large"
                ];
                array_push($sizePickerArray, $size);
            }
            return response()->json([
                "product_data" => $product_data, "small" => $product_data_small,
                "medium" => $product_data_medium, "large" => $product_data_large,
                "sizePicker" => $sizePickerArray, "title_sku" => $product_data_title_sku
            ]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }
}
