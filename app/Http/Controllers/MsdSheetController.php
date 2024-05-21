<?php

namespace App\Http\Controllers;

use App\Models\MsdSheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MsdSheetController extends Controller
{
    /**
     * The function `allMsdsSheets` retrieves all MSD sheets in descending order of their IDs and
     * returns them as a JSON response, handling any errors that may occur.
     * 
     * @return The `allMsdsSheets` function is returning a JSON response containing all MSD sheets
     * retrieved from the database, ordered by their IDs in descending order. If an error occurs during
     * the retrieval process, it will return a JSON response with an error message and a status code of
     * 400.
     */
    public function allMsdsSheets()
    {
        try {
            $all_msd = MsdSheet::orderBy('id', 'DESC')->get();
            return response()->json(['all_msds' => $all_msd]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * The function `customerAllMsdsSheets` retrieves all active MSD sheets in descending order of ID
     * and returns them as a JSON response.
     * 
     * @return The `customerAllMsdsSheets` function returns a JSON response containing all MSD sheets
     * with a status of 'Active', ordered by ID in descending order. If the retrieval is successful, it
     * returns the JSON response with the MSD sheets data under the key 'all_msds'. If an error occurs
     * during the retrieval process, it returns a JSON response with an error message under the key
     * 'error'
     */
    public function customerAllMsdsSheets()
    {
        try {
            $all_msd = MsdSheet::whereStatus('Active')->orderBy('id', 'DESC')->get();
            return response()->json(['all_msds' => $all_msd]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * This PHP function retrieves and returns the details of an MSD sheet based on the provided ID.
     * 
     * @param Request request The `msdSheetDetail` function is a PHP function that takes a `Request`
     * object as a parameter. The `Request` object is typically used in Laravel applications to handle
     * incoming HTTP requests and retrieve input data from forms or requests.
     * 
     * @return The `msdSheetDetail` function is returning a JSON response. If the request parameter
     * 'id' is provided and passes validation, it attempts to find an `MsdSheet` record with the
     * corresponding ID. If found, it returns a JSON response containing the details of the found
     * `MsdSheet`. If an error occurs during the process, it returns a JSON response with the error
     * message.
     */
    public function msdSheetDetail(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);
        try {
            $msdSheet = MsdSheet::find($request->id);
            return response()->json(['sheet_detail' => $msdSheet]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * The function `addMsdSheet` in PHP validates and stores MSD sheet files and images, categorizing
     * the file type and saving the data in the database.
     * 
     * @param Request request The `addMsdSheet` function is a controller method that handles the
     * addition of a new MSD sheet. Let's break down the code and explain what each part does:
     * 
     * @return The function `addMsdSheet` is returning a JSON response. If the MsdSheet is successfully
     * added, it returns a JSON response with a success message "MsdSheet successfully added". If there
     * is an error during the process, it returns a JSON response with the error message obtained from
     * the exception thrown.
     */
    public function addMsdSheet(Request $request)
    {
        $validate = $request->validate([
            'title' => 'required',
            'description' => 'required',
            'file' => 'required|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx',
        ]);

        try {
            $validate['status'] = 'Active';
            $new_name = time() . '.' . $request->file->extension();
            // $request->file->move(public_path('storage/msdSheets'), $new_name);
            // $validate['file'] = "storage/msdSheets/$new_name";
            $path = $request->file('file')->storeAs('msdSheets', $new_name, 's3');
            $validate['file'] = $path;

            $file_type = strtolower($request->file->getClientOriginalExtension());
            $pdf_extension = ['pdf'];
            $word_extension = ['doc', 'docx'];
            $ppt_extension = ['ppt', 'pptx'];
            $excel_extension = ['xls', 'xlsx'];

            // if ($request->has('image')) {
            //     $new_name = time() . '.' . $request->image->extension();
            //     $request->image->move(public_path('storage/msdSheets/images'), $new_name);
            //     $validate['image'] = "storage/msdSheets/images/$new_name";
            // }
            if ($request->has('image')) {
                $new_name = time() . '.' . $request->image->extension();
                $path = $request->file('image')->storeAs('msdSheets/images', $new_name, 's3');
                // $path = "https://vrc-bucket.s3.us-east-2.amazonaws.com/$path";
                // $path = "https://greenboom-bucket.s3.us-east-2.amazonaws.com/$path";
                $validate['image'] = $path;
            }

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

            MsdSheet::create($validate);
            return response()->json(["message" => "MsdSheet successfully added"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }


    /**
     * The function `updateMsdSheet` updates an existing MsdSheet record with new title, description,
     * file, and image, handling file uploads and storage on AWS S3.
     * 
     * @param Request request Based on the provided code snippet, the `updateMsdSheet` function is
     * responsible for updating an existing MSD (Material Safety Data) sheet. It takes a `Request`
     * object as a parameter, which likely contains the necessary data for updating the MSD sheet.
     * 
     * @return The `updateMsdSheet` function returns a JSON response with a success message if the MSD
     * sheet is successfully updated, or an error message if an exception occurs during the update
     * process.
     */
    public function updateMsdSheet(Request $request)
    {
        try {
            $msd = MsdSheet::find($request->id);

            if (!$msd) {
                return response()->json(["error" => "Msd Sheet not found"], 404);
            }

            $msd->title = $request->title;
            $msd->description = $request->description;

            if ($request->hasFile('file')) {
                $new_name = time() . '.' . $request->file->extension();
                // $request->file->move(public_path('storage/msdSheets'), $new_name);

                $path = $request->file('file')->storeAs('msdSheets', $new_name, 's3');

                // Use unlink for direct file deletion
                if (file_exists($msd->file)) {
                    unlink($msd->file);
                }

                // $msd->file = "storage/msdSheets/$new_name";
                $msd->file = $path;

                $file_type = strtolower($request->file->getClientOriginalExtension());
                $pdf_extension = ['pdf'];
                $word_extension = ['doc', 'docx'];
                $ppt_extension = ['ppt', 'pptx'];
                $excel_extension = ['xls', 'xlsx'];

                if (in_array($file_type, $pdf_extension)) {
                    $msd->file_type = 'pdf';
                } elseif (in_array($file_type, $word_extension)) {
                    $msd->file_type = 'word';
                } elseif (in_array($file_type, $ppt_extension)) {
                    $msd->file_type = 'ppt';
                } elseif (in_array($file_type, $excel_extension)) {
                    $msd->file_type = 'excel';
                } else {
                    $msd->file_type = 'other';
                }
            }
            if ($request->has('image')) {
                $new_name = time() . '.' . $request->image->extension();
                // $request->image->move(public_path('storage/msdSheets/images'), $new_name);
                // $msd->image = "storage/msdSheets/images/$new_name";
                $path = $request->file('image')->storeAs('msdSheets/images', $new_name, 's3');
                // $path = "https://vrc-bucket.s3.us-east-2.amazonaws.com/$path";
                // $path = "https://greenboom-bucket.s3.us-east-2.amazonaws.com/$path";
                $msd->image = $path;
            }

            $msd->save();
            return response()->json(['message' => 'Msd sheet successfully updated'], 200);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * The function `deleteMsdSheet` deletes a specific MsdSheet record along with its associated file.
     * 
     * @param id The `deleteMsdSheet` function you provided is used to delete an MSD sheet by its ID.
     * The function first attempts to find the MSD sheet with the given ID. If the MSD sheet is not
     * found, it returns a JSON response with an error message and a status code of 404.
     * 
     * @return The function `deleteMsdSheet` returns a JSON response with either a success message if
     * the MSD sheet was successfully deleted or an error message if an exception occurred during the
     * deletion process.
     */
    public function deleteMsdSheet($id)
    {
        try {
            $msd = MsdSheet::find($id);
            if (!$msd) {
                return response()->json(["error" => "MsdSheet not found"], 404);
            }
            $filePath = $msd->file;
            // Use unlink for direct file deletion
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $msd->delete();
            return response()->json(["message" => "MsdSheet successfully deleted"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }


    /**
     * The function `msdStatus` toggles the status of a `MsdSheet` between "Active" and "InActive".
     * 
     * @param id The `msdStatus` function you provided is responsible for toggling the status of an
     * `MsdSheet` entity between "Active" and "Inactive" based on the provided `id`. If the `MsdSheet`
     * with the given `id` exists, its status will be updated accordingly
     * 
     * @return The `msdStatus` function returns a JSON response with a message indicating whether the
     * status of the MSD sheet has been successfully changed or if there was an error. If the MSD sheet
     * with the provided ID is not found, it returns a message stating "Invalid msdSheet". If the
     * status of the MSD sheet is successfully toggled between "Active" and "Inactive", it returns a
     * message saying
     */
    public function msdStatus($id)
    {
        try {
            $msd_sheet = MsdSheet::find($id);
            if (!$msd_sheet)
                return response()->json(["message" => "Invalid msdSheet"]);
            if ($msd_sheet->status == "Active") {
                $msd_sheet->status = "InActive";
            } else {
                $msd_sheet->status = "Active";
            }
            $msd_sheet->save();
            return response()->json(["message" => "MsdSheet status changed"], 200);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }
}
