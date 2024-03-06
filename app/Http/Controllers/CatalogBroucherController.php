<?php

namespace App\Http\Controllers;

use App\Models\CatalogBroucher;
use Illuminate\Http\Request;

class CatalogBroucherController extends Controller
{

    /**
     * This PHP function retrieves all catalogs from the database in descending order of their IDs and
     * returns them as a JSON response, handling any errors that may occur.
     * 
     * @return The `allCatalogs` function returns a JSON response containing all the catalogs fetched
     * from the database in descending order of their IDs. If an error occurs during the retrieval
     * process, it will return a JSON response with the error message and a status code of 400.
     */
    public function allCatalogs()
    {
        try {
            $all_catalogs = CatalogBroucher::orderBy('id', 'DESC')->get();
            return response()->json(['all_catalogs' => $all_catalogs]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * This PHP function retrieves all active catalogs from the database and returns them as a JSON
     * response.
     * 
     * @return The `customerAllCatalogs` function is returning a JSON response. If the operation is
     * successful, it returns a JSON object containing all the catalogs that have a status of 'Active',
     * ordered by ID in descending order. The key for the catalogs array is 'all_catalogs'. If an error
     * occurs during the execution of the function, it returns a JSON object with an 'error' key
     * containing the
     */
    public function customerAllCatalogs()
    {
        try {
            $all_catalogs = CatalogBroucher::whereStatus('Active')->orderBy('id', 'DESC')->get();
            return response()->json(['all_catalogs' => $all_catalogs]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * This PHP function retrieves and returns details of a catalog brochure based on the provided ID.
     * 
     * @param Request request The `CatalogDetail` function is a PHP function that takes a `Request`
     * object as a parameter. The function then validates the request to ensure that it contains a
     * parameter with the key 'id'. If the validation passes, it tries to find a catalog brochure with
     * the provided 'id' using the
     * 
     * @return The CatalogDetail function returns a JSON response containing the details of a catalog
     * brochure with the specified ID. If the catalog is found successfully, it returns the catalog
     * details in the response. If an error occurs during the process, it returns a JSON response with
     * an error message and a status code of 400.
     */
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

    /**
     * The function `addCatalogBroucher` validates and stores a catalog broucher file, categorizes its
     * type, and creates a new entry in the database.
     * 
     * @param Request request The `addCatalogBroucher` function is used to handle the addition of a new
     * catalog brochure. Let me explain the code step by step:
     * 
     * @return The function `addCatalogBroucher` is returning a JSON response. If the operation is
     * successful, it returns a JSON response with a message indicating that the CatalogBroucher was
     * successfully added. If there is an error during the process, it returns a JSON response with an
     * error message extracted from the exception thrown.
     */
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


    /**
     * The function `updateCatalogBroucher` updates a catalog brochure's title, description, and file,
     * handling file uploads and determining file type.
     * 
     * @param Request request The `updateCatalogBroucher` function is responsible for updating a
     * catalog brochure in the database based on the provided request data. Let's break down the code
     * and explain each part:
     * 
     * @return The function `updateCatalogBroucher` is returning a JSON response with a success message
     * if the catalogBroucher is successfully updated, along with an HTTP status code of 200. If there
     * is an error during the update process, it returns a JSON response with the error message from
     * the exception caught in the try-catch block, along with an HTTP status code of 400.
     */
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

    /**
     * This PHP function deletes a catalog brochure by finding it, deleting its associated file, and
     * then deleting the catalog entry itself.
     * 
     * @param id The `deletecatalogBroucher` function is used to delete a catalog brochure by its ID.
     * The function first attempts to find the catalog brochure with the provided ID. If the catalog
     * brochure is not found, it returns a JSON response with an error message and a status code of
     * 404.
     * 
     * @return The function `deletecatalogBroucher` is returning a JSON response. If the operation is
     * successful, it returns a JSON response with a message indicating that the CatalogBroucher was
     * successfully deleted. If there is an error, it returns a JSON response with an error message.
     */
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


    /**
     * The function `catalogStatus` toggles the status of a catalog brochure between "Active" and
     * "Inactive".
     * 
     * @param id The `catalogStatus` function you provided is responsible for toggling the status of a
     * catalog brochure between "Active" and "Inactive" based on the provided ID. If the current status
     * is "Active", it will be changed to "Inactive", and vice versa.
     * 
     * @return The `catalogStatus` function returns a JSON response with a message indicating whether
     * the status of the CatalogBroucher has been successfully changed or if there was an error. If the
     * CatalogBroucher with the provided ID is found, its status is toggled between "Active" and
     * "InActive" and then saved. The function returns a success message if the status is changed
     * successfully, or an
     */
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
