<?php

namespace App\Http\Controllers;

use App\Models\MsdSheet;
use App\Models\Product;
use App\Models\ProductData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Here getting all product list in descending order by id
     */

    public function allProducts()
    {
        try {
            $all_products = Product::orderBy('id', 'DESC')->get();
            return response()->json(["all_products" => $all_products]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * Here getting all product that have status Active with product data in descending order by id
     * and this function is use for customer
     */
    public function customerAllProducts()
    {
        try {
            $all_products = Product::whereStatus('Active')->with('productData')->orderBy('id', 'DESC')->get();
            return response()->json(["all_products" => $all_products]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * Here getting all product data list
     * In param sending product id through that, data will get all product data where that id matches
     */
    public function productData($id)
    {
        try {
            $product = Product::find($id);
            if (!$product)
                return response()->json(["message" => "Invalid product"]);
            $product_data = ProductData::whereProductId($id)->get();
            $product_data_small = ProductData::whereProductId($id)->whereSize('small')->get();
            $product_data_medium = ProductData::whereProductId($id)->whereSize('medium')->get();
            $product_data_large = ProductData::whereProductId($id)->whereSize('large')->get();
            if (!$product_data_small) {
                $product_data_small = [];
            } else
                $sizePickerArray = [];

            if ($product_data_small->isEmpty()) {
                $product_data_small = [];
            } else {
                $size = [
                    "id" => "small",
                    "title" => "small"
                ];
                array_push($sizePickerArray, $size);
            }

            if ($product_data_medium->isEmpty()) {
                $product_data_medium = [];
            } else {
                $size = [
                    "id" => "medium",
                    "title" => "medium"
                ];
                array_push($sizePickerArray, $size);
            }

            if ($product_data_large->isEmpty()) {
                $product_data_large = [];
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
                "sizePicker" => $sizePickerArray
            ]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }


    /**
     * Here getting product detail of given product id
     */
    public function productDetail(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);
        try {
            $product = Product::find($request->id);
            return response()->json(['product_detail' => $product]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * Here adding product
     */

    public function addProduct(Request $request)
    {
        $validate = $request->validate([
            'product_name' => 'required',
            // 'usage' => 'required',
            'title' => 'required',
            // 'description' => 'required',
            'product_type' => 'required',
            'file' => 'required|mimes:jpg,jpeg,png',
        ]);

        try {
            $validate['usage'] = $request->usage;
            $validate['description'] = $request->description;

            $validate['status'] = 'Active';
            // $path = $request->file('file')->store('newProducts/images', 's3');
            // $path = "https://vrc-bucket.s3.us-east-2.amazonaws.com/$path";
            // $validate['file'] = $path;
            $file = $request->file('file');
            $new_name = time() . '.' . $file->extension();
            // $file->move(public_path('storage/products'), $new_name);
            // $validate['file'] = "storage/products/$new_name";
            $path = $request->file('file')->storeAs('products', $new_name, 's3');
            $validate['file'] = $path;
            Product::create($validate);

            return response()->json(['message' => 'Product successfully added']);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * Here updating product
     */

    public function updateProduct(Request $request)
    {
        $validate = $request->validate([
            'id' => 'required',
            'product_name' => 'required',
            'usage' => 'required',
            'title' => 'required',
            'description' => 'required',
            'file' => 'required|mimes:jpg,jpeg,png',
        ]);

        try {
            $validate['status'] = 'Active';

            $existingProduct = Product::find($request->id);

            if ($existingProduct) {
                if ($request->hasFile('file')) {
                    $new_name = time() . '.' . $request->file->extension();
                    Storage::delete($existingProduct->file);
                    // $validate['file'] = $request->file('file')->store('public/products');
                    $path = $request->file('file')->storeAs('products', $new_name, 's3');
                    $validate['file'] = $path;
                }

                $existingProduct->update($validate);
                return response()->json(['message' => 'Product successfully updated']);
            } else {
                return response()->json(['error' => 'Product not found'], 404);
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 400);
        }
    }

    /**
     * Delete a product and its associated file from the database and storage.
     *
     * @param int $id The ID of the product to be deleted.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the result of the deletion process.
     */
    public function deleteProduct($id)
    {
        try {
            // Find the product by its ID
            $product = Product::find($id);

            // If the product doesn't exist, return a 404 error response
            if (!$product) {
                return response()->json(["error" => "Product not found"], 404);
            }

            // Retrieve the file path associated with the product
            $filePath = $product->file;

            // Delete the product from the database
            $product->delete();

            // Delete the associated file from storage
            Storage::delete($filePath);

            // Return a success message in JSON format
            return response()->json(["message" => "Product successfully deleted"]);
        } catch (\Throwable $th) {
            // If an error occurs during the deletion process, return a 400 error response
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }



    /**
     * Toggle the status of a product between Active and Inactive.
     *
     * @param int $id The ID of the product whose status is to be toggled.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the result of the status toggle operation.
     */
    public function productStatus($id)
    {
        try {
            // Find the product by its ID
            $product = Product::find($id);

            // If the product doesn't exist, return a JSON response indicating an invalid product
            if (!$product)
                return response()->json(["message" => "Invalid Product"]);

            // Toggle the status of the product between Active and Inactive
            if ($product->status == "Active") {
                $product->status = "InActive";
            } else {
                $product->status = "Active";
            }

            // Save the updated status of the product
            $product->save();

            // Return a JSON response indicating that the product status has been successfully changed
            return response()->json(["message" => "Product status changed"], 200);
        } catch (\Throwable $th) {
            // If an error occurs during the status toggle operation, return a JSON response with a 400 status containing the error message
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }
}
