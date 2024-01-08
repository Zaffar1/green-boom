<?php

namespace App\Http\Controllers;

use App\Models\MsdSheet;
use App\Models\Product;
use App\Models\ProductData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function allProducts()
    {
        try {
            $all_products = Product::whereStatus('Active')->orderBy('id', 'DESC')->get();
            return response()->json(["all_products" => $all_products]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function customerAllProducts()
    {
        try {
            $all_products = Product::whereStatus('Active')->with('productData')->orderBy('id', 'DESC')->get();
            return response()->json(["all_products" => $all_products]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }


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
            }
            if (!$product_data_medium) {
                $product_data_medium = [];
            }
            if (!$product_data_large) {
                $product_data_large = [];
            }
            return response()->json([
                "product_data" => $product_data, "product_small" => $product_data_small,
                "product_medium" => $product_data_medium, "product_large" => $product_data_large
            ]);
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
            'file' => 'required|mimes:jpg,jpeg,png',
        ]);

        try {
            $validate['status'] = 'Active';
            $file = $request->file('file');
            $new_name = time() . '.' . $file->extension();
            $file->move(public_path('storage/products'), $new_name);
            $validate['file'] = "storage/products/$new_name";
            Product::create($validate);

            return response()->json(['message' => 'Product successfully added']);
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

            $existingProduct = Product::find($request->id);

            if ($existingProduct) {
                if ($request->hasFile('file')) {
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
