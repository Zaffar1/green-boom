<?php

namespace App\Http\Controllers;

use App\Models\CatalogBroucher;
use Illuminate\Http\Request;

class CatalogBroucherController extends Controller
{

    public function allCatalogs()
    {
        try {
            $all_catalogs = CatalogBroucher::orderBy('id', 'DESC')->get();
            return response()->json(['all_catalogs' => $all_catalogs]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function customerAllCatalogs()
    {
        try {
            $all_catalogs = CatalogBroucher::whereStatus('Active')->orderBy('id', 'DESC')->get();
            return response()->json(['all_catalogs' => $all_catalogs]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function CatalogDetail(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);
        try {
            $catalog = CatalogBroucher::find($request->id);
            return response()->json(['catalog_detail' => $catalog]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function addCatalogBroucher(Request $request)
    {
        $validate = $request->validate([
            'title' => 'required',
            'description' => 'required',
            'file' => 'required|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx',
        ]);

        try {
            $validate['status'] = 'Active';
            $new_name = time() . '.' . $request->file->extension();
            $path = $request->file('file')->storeAs('catalogBrouchers', $new_name, 's3');
            $validate['file'] = $path;
            // $request->file->move(public_path('storage/catalogBrouchers'), $new_name);
            // $validate['file'] = "storage/catalogBrouchers/$new_name";

            $file_type = strtolower($request->file->getClientOriginalExtension());
            $pdf_extension = ['pdf'];
            $word_extension = ['doc', 'docx'];
            $ppt_extension = ['ppt', 'pptx'];
            $excel_extension = ['xls', 'xlsx'];

            if (in_array($file_type, $pdf_extension)) {
                $validate['file_type'] = 'pdf';
            } elseif (in_array($file_type, $word_extension)) {
                $validate['file_type'] = 'word';
            } elseif (in_array($file_type, $ppt_extension)) {
                $validate['file_type'] = 'ppt';
            } elseif (in_array($file_type, $excel_extension)) {
                $validate['file_type'] = 'excel';
            } else {
                $validate['file_type'] = 'other';
            }

            CatalogBroucher::create($validate);
            return response()->json(["message" => "CatalogBroucher successfully added"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }


    public function updateCatalogBroucher(Request $request)
    {
        try {
            $catalog = CatalogBroucher::find($request->id);

            if (!$catalog) {
                return response()->json(["error" => "catalogBrouchers not found"], 404);
            }

            $catalog->title = $request->title;
            $catalog->description = $request->description;

            if ($request->hasFile('file')) {
                $new_name = time() . '.' . $request->file->extension();
                // $request->file->move(public_path('storage/catalogBrouchers'), $new_name);
                $path = $request->file('file')->storeAs('catalogBrouchers', $new_name, 's3');
                $catalog->file = $path;
                // Use unlink for direct file deletion
                if (file_exists($catalog->file)) {
                    unlink($catalog->file);
                }

                // $catalog->file = "storage/catalogBrouchers/$new_name";

                $file_type = strtolower($request->file->getClientOriginalExtension());
                $pdf_extension = ['pdf'];
                $word_extension = ['doc', 'docx'];
                $ppt_extension = ['ppt', 'pptx'];
                $excel_extension = ['xls', 'xlsx'];

                if (in_array($file_type, $pdf_extension)) {
                    $catalog->file_type = 'pdf';
                } elseif (in_array($file_type, $word_extension)) {
                    $catalog->file_type = 'word';
                } elseif (in_array($file_type, $ppt_extension)) {
                    $catalog->file_type = 'ppt';
                } elseif (in_array($file_type, $excel_extension)) {
                    $catalog->file_type = 'excel';
                } else {
                    $catalog->file_type = 'other';
                }
            }

            $catalog->save();
            return response()->json(['message' => 'catalogBroucher successfully updated'], 200);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function deletecatalogBroucher($id)
    {
        try {
            $catalog = CatalogBroucher::find($id);
            if (!$catalog) {
                return response()->json(["error" => "CatalogBroucher not found"], 404);
            }
            $filePath = $catalog->file;
            // Use unlink for direct file deletion
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $catalog->delete();
            return response()->json(["message" => "CatalogBroucher successfully deleted"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }


    public function catalogStatus($id)
    {
        try {
            $catalog = CatalogBroucher::find($id);
            if (!$catalog)
                return response()->json(["message" => "Invalid catalogBroucher"]);
            if ($catalog->status == "Active") {
                $catalog->status = "InActive";
            } else {
                $catalog->status = "Active";
            }
            $catalog->save();
            return response()->json(["message" => "CatalogBroucher status changed"], 200);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }
}
