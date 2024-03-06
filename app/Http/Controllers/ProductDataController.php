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
    /**
     * Retrieve all product data from the database.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing all product data.
     */
    public function allProductData()
    {
        try {
            // Retrieve all product data from the database, ordered by ID in descending order
            $all_product_data = ProductData::orderBy('id', 'DESC')->get();

            // Return a JSON response containing all product data
            return response()->json(["all_product_data" => $all_product_data]);
        } catch (\Throwable $th) {
            // If an error occurs during the retrieval process, return a JSON response with a 400 status containing the error message
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }


    /**
     * Add product data to the database.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing the product data.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the result of the product data addition.
     */
    public function addProductData(Request $request)
    {
        // Validate the incoming request data
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
            // Find the product by its ID
            $product = Product::find($request->product_id);

            // If the product doesn't exist, return a JSON response indicating an invalid product
            if (!$product) {
                return response()->json(['message' => 'Invalid product']);
            } else {
                // Set the status of the product data to Active
                $validate['status'] = 'Active';
            }

            // Create a new product data entry in the database
            ProductData::create($validate);

            // Return a JSON response indicating that the product data has been successfully added
            return response()->json(["message" => "Product Data successfully added"], 200);
        } catch (\Throwable $th) {
            // If an error occurs during the addition process, return a JSON response with a 400 status containing the error message
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }


    /**
     * Retrieve the data associated with a specific product.
     *
     * @param int $id The ID of the product for which data is to be retrieved.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the data associated with the specified product.
     */
    public function productData($id)
    {
        try {
            // Find the product by its ID
            $product = Product::find($id);

            // If the product doesn't exist, return a JSON response indicating an invalid product
            if (!$product)
                return response()->json(["message" => "Invalid product"]);

            // Retrieve the data associated with the product from the ProductData model, ordered by ID in descending order
            $productData = ProductData::whereProductId($product->id)->orderBy('id', 'DESC')->get();

            // Return a JSON response containing the retrieved data associated with the product
            return response()->json(["data" => $productData]);
        } catch (\Throwable $th) {
            // If an error occurs during the retrieval process, return a JSON response with a 400 status containing the error message
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }
}
