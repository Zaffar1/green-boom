<?php

namespace App\Http\Controllers;

use App\Models\PerfectSaleMedia;
use App\Models\ScriptData;
use Illuminate\Http\Request;

class ScriptController extends Controller
{
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
