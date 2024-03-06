<?php

namespace App\Http\Controllers;

use App\Models\PerfectSaleMedia;
use App\Models\ScriptData;
use Illuminate\Http\Request;

class ScriptController extends Controller
{
    /**
     * Add script data to the database.
     *
     * This function adds script data to the database based on the provided request data.
     * The request data must include the script ID, title, file, and icon type of the script data.
     * It validates the file type to ensure it is one of the allowed formats (pdf, mp4, mov, avi, doc, docx, ppt, pptx, xls, xlsx).
     * If the specified script data is not found, it returns a JSON response indicating that the data is invalid.
     * If the script data is found, it associates the script data with the script ID and sets the status to "Active".
     * It uploads the file to the configured S3 storage with a unique filename generated based on the current timestamp.
     * It determines the type of the file (video, pdf, word, ppt, excel, or other) based on its extension.
     * If successful, it returns a JSON response indicating that the script data has been successfully added.
     * If any error occurs during the addition process, it catches the exception and returns a JSON response with a 400 status containing the error message.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing the script data.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the result of the script data addition.
     */

    public function addScriptData(Request $request)
    {
        $validate = $request->validate([
            'script_id' => 'required',
            'title' => 'required',
            'file' => 'required|mimes:pdf,mp4,mov,avi,doc,docx,ppt,pptx,xls,xlsx',
            'icon_type' => 'required',
        ]);

        try {
            $script_data = PerfectSaleMedia::find($request->script_id);

            if (!$script_data) {
                return response()->json(['message' => 'Invalid Data']);
            } else {
                $validate['perfect_sale_media_id'] = $request->script_id;
                $validate['status'] = 'Active';
            }

            $file = $request->file('file');
            $new_name = time() . '.' . $file->extension();
            $path = $request->file('file')->storeAs('scriptData', $new_name, 's3');
            $validate['file'] = $path;
            // $file->move(public_path('storage/scriptData'), $new_name);
            // $validate['file'] = "storage/scriptData/$new_name";

            $file_type = $file->getClientOriginalExtension();

            $video_extension = ['mp4', 'avi', 'mov', 'wmv'];
            $pdf_extension = ['pdf'];
            $word_extension = ['doc', 'docx'];
            $ppt_extension = ['ppt', 'pptx'];
            $excel_extension = ['xls', 'xlsx'];

            if (in_array(strtolower($file_type), $video_extension)) {
                $validate['file_type'] = 'video';
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

            ScriptData::create($validate);
            return response()->json(["message" => "Script data successfully added"], 200);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * Update script data in the database.
     *
     * This function updates script data in the database based on the provided request data.
     * The request data must include the ID and title of the script data.
     * If the specified script data is not found, it returns a JSON response indicating that the data is invalid.
     * If a new file is provided in the request, it deletes the old file associated with the script data from storage and uploads the new file to the configured S3 storage with a unique filename generated based on the current timestamp.
     * It determines the type of the file (video, pdf, word, ppt, excel, or other) based on its extension.
     * If successful, it returns a JSON response indicating that the script data has been successfully updated.
     * If any error occurs during the update process, it catches the exception and returns a JSON response with a 400 status containing the error message.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing the updated script data.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the result of the script data update.
     */

    public function updateScriptData(Request $request)
    {
        $validate = $request->validate([
            'id' => 'required',
            'title' => 'required',
        ]);
        try {
            $script_data = ScriptData::find($request->id);
            if (!$script_data)
                return response()->json(["message" => "Invalid script data"]);
            if ($request->hasFile('file')) {
                // Delete the old file from storage
                if (file_exists($script_data->file)) {
                    unlink($script_data->file);
                }

                $file = $request->file('file');
                $new_name = time() . '.' . $file->extension();
                $path = $request->file('file')->storeAs('scriptData', $new_name, 's3');
                $script_data->file = $path;
                // $file->move(public_path('storage/perfectSaleMedia'), $new_name);
                // $script_data->file = "storage/perfectSaleMedia/$new_name";

                $file_type = $file->getClientOriginalExtension();

                $video_extension = ['mp4', 'avi', 'mov', 'wmv'];
                $pdf_extension = ['pdf'];
                $word_extension = ['doc', 'docx'];
                $ppt_extension = ['ppt', 'pptx'];
                $excel_extension = ['xls', 'xlsx'];

                if (in_array(strtolower($file_type), $video_extension)) {
                    $script_data->file_type = 'video';
                } elseif (in_array(strtolower($file_type), $pdf_extension)) {
                    $script_data->file_type = 'pdf';
                } elseif (in_array(strtolower($file_type), $word_extension)) {
                    $script_data->file_type = 'word';
                } elseif (in_array(strtolower($file_type), $ppt_extension)) {
                    $script_data->file_type = 'ppt';
                } elseif (in_array(strtolower($file_type), $excel_extension)) {
                    $script_data->file_type = 'excel';
                } else {
                    $script_data->file_type = 'other';
                }
            }
            $script_data->update($validate);
            return response()->json(["message" => "Perfect Sale successfully updated"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * Delete script data from the database.
     *
     * This function deletes script data from the database based on the provided ID.
     * If the specified script data is not found, it returns a JSON response indicating that the data is invalid.
     * If successful, it returns a JSON response indicating that the script data has been successfully deleted.
     * If any error occurs during the deletion process, it catches the exception and returns a JSON response with a 400 status containing the error message.
     *
     * @param int $id The ID of the script data to be deleted.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the result of the script data deletion.
     */

    public function deleteScriptData($id)
    {
        try {
            $script_data = ScriptData::find($id);
            if (!$script_data)
                return response()->json(["message" => "Invalid script data"]);
            $script_data->delete();
            return response()->json(["message" => "Script data deleted"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * Toggle the status of script data between Active and Inactive.
     *
     * This function toggles the status of script data between "Active" and "Inactive" based on the provided ID.
     * If the specified script data is not found, it returns a JSON response with a 404 status indicating that the script data is invalid.
     * If the script data is found, it toggles its status accordingly and saves the changes to the database.
     * If successful, it returns a JSON response indicating that the script data status has been successfully changed.
     * If any error occurs during the status toggle operation, it catches the exception and returns a JSON response with a 400 status containing the error message.
     *
     * @param int $id The ID of the script data whose status is to be toggled.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the result of the status toggle operation.
     */
    public function ScriptDataStatus($id)
    {
        // $request->validate([
        //     "id" => "required",
        // ]);
        try {
            $script_data = ScriptData::find($id);
            if (!$script_data)
                return response()->json(["message" => "Invalid script data"]);
            if ($script_data->status == "Active") {
                $script_data->status = "InActive";
            } else {
                $script_data->status = "Active";
            }
            $script_data->save();
            return response()->json(["message" => "Script data status changed"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }
}
