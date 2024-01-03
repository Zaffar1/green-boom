<?php

namespace App\Http\Controllers;

use App\Models\MsdSheet;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function allProducts()
    {
        try {
            $all_products = Product::orderBy('id', 'DESC')->get();
            return response()->json(["all_products" => $all_products]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function customerAllProducts()
    {
        try {
            $all_products = Product::whereStatus('Active')->orderBy('id', 'DESC')->get();
            return response()->json(["all_products" => $all_products]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

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


    public function addProduct(Request $request)
    {
        $validate = $request->validate([
            'product_name' => 'required',
            'usage' => 'required',
            'title' => 'required',
            'description' => 'required',
            'file.*' => 'required|mimes:jpg,jpeg,png', // Use files.* to validate each file in the array
        ]);

        try {
            $validate['status'] = 'Active';

            // Create the product
            $product = Product::create($validate);

            // Handle multiple images
            if ($request->hasFile('file')) {
                foreach ($request->file('file') as $file) {
                    $new_name = time() . '_' . $file->getClientOriginalName();
                    $file->move(public_path('storage/products'), $new_name);

                    // Create a new image record for the product
                    $product->images()->create([
                        'path' => "storage/products/$new_name",
                    ]);
                }
            }

            return response()->json(['message' => 'Product successfully added with images']);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

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

            // Find the product by ID
            $existingProduct = Product::find($request->id);

            if ($existingProduct) {
                // Update the existing product
                if ($request->hasFile('file')) {
                    // Update the file only if a new file is provided
                    Storage::delete($existingProduct->file);
                    $validate['file'] = $request->file('file')->store('public/products');
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

    public function deleteProduct($id)
    {
        try {
            $product = Product::find($id);
            if (!$product) {
                return response()->json(["error" => "Product not found"], 404);
            }
            $filePath = $product->file;
            $product->delete();
            Storage::delete($filePath);
            return response()->json(["message" => "Product successfully deleted"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }
}
