<?php

namespace App\Http\Controllers;

use App\Models\PerfectSale;
use App\Models\PerfectSaleMedia;
use App\Models\ScriptData;
use Illuminate\Http\Request;

class PerfectSaleMediaController extends Controller
{
    /**
     * The function `addPerfectSaleMedia` in PHP validates and stores media files related to a Perfect
     * Sale, categorizing them based on file type.
     * 
     * @param Request request The `addPerfectSaleMedia` function in the provided code snippet is
     * responsible for adding media files related to a perfect sale. Let me explain the code step by
     * step:
     * 
     * @return The function `addPerfectSaleMedia` is returning a JSON response with a success message
     * "Perfect sale data successfully added" and a status code of 200 if the data is successfully
     * added to the database. If there is an error during the process, it will return a JSON response
     * with an error message containing the exception message and a status code of 400.
     */
    public function addPerfectSaleMedia(Request $request)
    {
        $validate = $request->validate([
            'perfect_sale_id' => 'required',
            'title' => 'required',
            'file' => 'required|mimes:pdf,mp4,mov,avi,doc,docx,ppt,pptx,xls,xlsx',
        ]);

        try {
            $perfect_sale = PerfectSale::find($request->perfect_sale_id);

            if (!$perfect_sale) {
                return response()->json(['message' => 'Invalid Data']);
            } else {
                $validate['status'] = 'Active';
            }

            $file = $request->file('file');
            $new_name = time() . '.' . $file->extension();
            $path = $request->file('file')->storeAs('perfectSaleMedia', $new_name, 's3');
            $validate['file'] = $path;
            // $file->move(public_path('storage/perfectSaleMedia'), $new_name);
            // $validate['file'] = "storage/perfectSaleMedia/$new_name";

            $file_type = $file->getClientOriginalExtension();

            $video_extension = ['mp4', 'avi', 'mov', 'wmv'];
            $pdf_extension = ['pdf'];
            $word_extension = ['doc', 'docx'];
            $ppt_extension = ['ppt', 'pptx'];
            $excel_extension = ['xls', 'xlsx'];

            if (in_array(strtolower($file_type), $video_extension)) {
                $validate['file_type'] = 'video';
                $new_name = time() . '.' . $request->thumbnail->extension();
                // $request->thumbnail->move(public_path('storage/perfectSaleMedia/thumbnail'), $new_name);
                // $validate['thumbnail'] = "storage/perfectSaleMedia/thumbnail/$new_name";
                $thumb = $request->file('thumbnail')->storeAs('perfectSaleMedia/thumbnail', $new_name, 's3');
                $validate['thumbnail'] = $thumb;
            } elseif (in_array(strtolower($file_type), $pdf_extension)) {
                $validate['file_type'] = 'pdf';
            } elseif (in_array(strtolower($file_type), $word_extension)) {
                $validate['file_type'] = 'word';
            } elseif (in_array(strtolower($file_type), $ppt_extension)) {
                $validate['file_type'] = 'ppt';
            } elseif (in_array(strtolower($file_type), $excel_extension)) {
                $validate['file_type'] = 'excel';
            } else {
                $validate['file_type'] = 'other';
            }

            PerfectSaleMedia::create($validate);
            return response()->json(["message" => "Perfect sale data successfully added"], 200);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * The function `addPerfectSaleMediaScript` validates and adds media data for a perfect sale,
     * handling errors appropriately.
     * 
     * @param Request request The `addPerfectSaleMediaScript` function is used to add media script data
     * for a perfect sale. It expects a `Request` object as a parameter, which contains the following
     * data:
     * 
     * @return If the `addPerfectSaleMediaScript` function is successfully executed, it will return a
     * JSON response with the message "Perfect sale data successfully added" and a status code of 200.
     * If an error occurs during the execution, it will return a JSON response with the error message
     * from the exception caught, along with a status code of 400.
     */
    public function addPerfectSaleMediaScript(Request $request)
    {
        $validate = $request->validate([
            'perfect_sale_id' => 'required',
            'title' => 'required',
        ]);

        try {
            $perfect_sale = PerfectSale::find($request->perfect_sale_id);

            if (!$perfect_sale) {
                return response()->json(['message' => 'Invalid Data']);
            } else {
                $validate['status'] = 'Active';
            }
            PerfectSaleMedia::create($validate);
            return response()->json(["message" => "Perfect sale data successfully added"], 200);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * The function `updatePerfectSaleMedia` updates Perfect Sale media data including file upload and
     * type detection.
     * 
     * @param Request request The `updatePerfectSaleMedia` function is responsible for updating Perfect
     * Sale media data based on the provided request parameters. Let's break down the key aspects of
     * this function:
     * 
     * @return a JSON response. If the update operation is successful, it will return a JSON response
     * with a message "Perfect sale data successfully updated" and a status code of 200. If there is an
     * error during the update operation, it will return a JSON response with the error message and a
     * status code of 400.
     */
    public function updatePerfectSaleMedia(Request $request)
    {
        $request->validate([
            // Add your validation rules as needed
        ]);

        try {
            $media = PerfectSaleMedia::find($request->id);

            if (!$media) {
                return response()->json(["error" => "Perfect sale data not found"], 404);
            }

            $media->perfect_sale_id = $request->perfect_sale_id;
            $media->title = $request->title;

            if ($request->hasFile('file')) {
                // Delete the old file from storage
                if (file_exists($media->file)) {
                    unlink($media->file);
                }

                $file = $request->file('file');
                $new_name = time() . '.' . $file->extension();
                $path = $request->file('file')->storeAs('perfectSaleMedia', $new_name, 's3');
                $media->file = $path;
                // $file->move(public_path('storage/perfectSaleMedia'), $new_name);
                // $media->file = "storage/perfectSaleMedia/$new_name";

                $file_type = $file->getClientOriginalExtension();

                $video_extension = ['mp4', 'avi', 'mov', 'wmv'];
                $pdf_extension = ['pdf'];
                $word_extension = ['doc', 'docx'];
                $ppt_extension = ['ppt', 'pptx'];
                $excel_extension = ['xls', 'xlsx'];

                if (in_array(strtolower($file_type), $video_extension)) {
                    $media->file_type = 'video';
                } elseif (in_array(strtolower($file_type), $pdf_extension)) {
                    $media->file_type = 'pdf';
                } elseif (in_array(strtolower($file_type), $word_extension)) {
                    $media->file_type = 'word';
                } elseif (in_array(strtolower($file_type), $ppt_extension)) {
                    $media->file_type = 'ppt';
                } elseif (in_array(strtolower($file_type), $excel_extension)) {
                    $media->file_type = 'excel';
                } else {
                    $media->file_type = 'other';
                }
            }
            $thumb_path = $request->file('thumbnail')->storeAs('perfectSaleMedia/thumbnail', $new_name, 's3');
            $media->thumbnail = $thumb_path;

            $media->save();
            return response()->json(["message" => "Perfect sale data successfully updated"], 200);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }


    /**
     * This PHP function deletes a PerfectSaleMedia record by its ID and returns a JSON response
     * indicating success or failure.
     * 
     * @param id The `deletePerfectSaleMedia` function is used to delete a PerfectSaleMedia entry from
     * the database based on the provided `id` parameter. The function first attempts to find the
     * PerfectSaleMedia entry with the given `id`. If the entry is found, it is deleted from the
     * database and a
     * 
     * @return The `deletePerfectSaleMedia` function returns a JSON response. If the PerfectSaleMedia
     * with the given `` is found and successfully deleted, it returns a JSON response with the
     * message "Perfect sale media deleted". If the PerfectSaleMedia with the given `` is not found,
     * it returns a JSON response with the message "Invalid perfect sale media". If an error occurs
     * during the deletion
     */
    public function deletePerfectSaleMedia($id)
    {
        try {
            $perfect_sale = PerfectSaleMedia::find($id);
            if (!$perfect_sale)
                return response()->json(["message" => "Invalid perfect sale media"]);
            $perfect_sale->delete();
            return response()->json(["message" => "Perfect sale media deleted"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }


    /**
     * The function `PerfectSaleMediaStatus` toggles the status of a PerfectSaleMedia entity between
     * "Active" and "Inactive".
     * 
     * @param id The code you provided is a PHP function that toggles the status of a PerfectSaleMedia
     * object between "Active" and "Inactive" based on the provided ID. If the current status is
     * "Active", it will be changed to "Inactive", and vice versa.
     * 
     * @return The `PerfectSaleMediaStatus` function is returning a JSON response. If the operation is
     * successful, it returns a JSON response with the message "Perfect sale media status changed". If
     * an error occurs during the process, it returns a JSON response with the error message captured
     * from the exception.
     */
    public function PerfectSaleMediaStatus($id)
    {
        // $request->validate([
        //     "id" => "required",
        // ]);
        try {
            $perfect_sale = PerfectSaleMedia::find($id);
            if (!$perfect_sale)
                return response()->json(["message" => "Invalid perfect sale media"]);
            if ($perfect_sale->status == "Active") {
                $perfect_sale->status = "InActive";
            } else {
                $perfect_sale->status = "Active";
            }
            $perfect_sale->save();
            return response()->json(["message" => "Perfect sale media status changed"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }


    /////////////Script Work
    /**
     * The function scriptMedia retrieves script data based on a perfect sale media ID and returns it
     * as JSON response.
     * 
     * @param Request request The `scriptMedia` function takes two parameters: `` and ``.
     * @param id The `id` parameter in the `scriptMedia` function is used to retrieve a specific
     * PerfectSaleMedia record from the database based on the provided ID. This ID is then used to
     * fetch related ScriptData records associated with the PerfectSaleMedia.
     * 
     * @return The scriptMedia function returns a JSON response containing the data fetched from the
     * ScriptData model based on the provided PerfectSaleMedia ID. If the PerfectSaleMedia with the
     * given ID is not found, it returns a JSON response indicating that the perfect sale media is
     * invalid. If an error occurs during the process, it returns a JSON response with the error
     * message.
     */
    public function scriptMedia(Request $request, $id)
    {
        // $request->validate([
        //     'perfect_sale_media_id' => 'required',
        // ]);
        try {
            $perfect_sale = PerfectSaleMedia::find($id);
            if (!$perfect_sale)
                return response()->json(["message" => "Invalid perfect sale media"]);
            else
                $scriptData = ScriptData::wherePerfectSaleMediaId($perfect_sale->id)->orderBy('id', 'DESC')->get();
            return response()->json(["data" => $scriptData]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }
}
