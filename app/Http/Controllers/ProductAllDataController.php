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
            $product_small = ProductDataSize::whereProductId($id)->whereSize('small')->get();
            if ($product_small) {
                $product_data_small['size'] = $product_small;

                // Small size dimension
                $product_small_dimension = ProductDataDimension::whereProductDataSizeId($product_small['id'])->first();
                if ($product_small_dimension) {
                    $product_data_small['dimension'] = $product_small_dimension;
                } else {
                    $product_data_small['dimension'] = [];
                }

                // Small size title_sku
                $product_data_title_sku = ProductDataTitle::whereProductDataSizeId($product_small['id'])->first();
                if ($product_data_title_sku) {
                    $product_data_small['title'] = $product_data_title_sku;
                } else {
                    $product_data_small['title'] = [];
                }

                // Add small size to sizePickerArray
                $sizePickerArray[] = ["id" => "small", "title" => "small"];
            } else {
                $product_data_small = [];
            }

            // Medium size
            $product_medium = ProductDataSize::whereProductId($id)->whereSize('medium')->get();
            if ($product_medium) {
                // $product_data_medium['product_medium'] = $product_medium;
                $product_data_medium = $product_medium;


                // Medium size dimension
                $product_medium_dimension = ProductDataDimension::whereProductDataSizeId($product_medium['id'])->first();
                if ($product_medium_dimension) {
                    $product_data_medium['dimension'] = $product_medium_dimension;
                } else {
                    $product_data_medium['dimension'] = [];
                }

                // Medium size title_sku
                $product_data_title_sku = ProductDataTitle::whereProductDataSizeId($product_medium['id'])->first();
                if ($product_data_title_sku) {
                    $product_data_medium['title'] = $product_data_title_sku;
                } else {
                    $product_data_medium['title'] = [];
                }

                // Add medium size to sizePickerArray
                $sizePickerArray[] = ["id" => "medium", "title" => "medium"];
            } else {
                $product_data_medium = [];
            }

            // Large size
            $product_large = ProductDataSize::whereProductId($id)->whereSize('large')->get();
            if ($product_large) {
                $product_data_large['large'] = $product_large;

                // Large size dimension
                $product_large_dimension = ProductDataDimension::whereProductDataSizeId($product_large['id'])->first();
                if ($product_large_dimension) {
                    $product_data_large['dimension'] = $product_large_dimension;
                } else {
                    $product_data_large['dimension'] = [];
                }

                // Large size title_sku
                $product_data_title_sku = ProductDataTitle::whereProductDataSizeId($product_large['id'])->first();
                if ($product_data_title_sku) {
                    $product_data_large['title'] = $product_data_title_sku;
                } else {
                    $product_data_large['title'] = [];
                }

                // Add large size to sizePickerArray
                $sizePickerArray[] = ["id" => "large", "title" => "large"];
            } else {
                $product_data_large = [];
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
}
