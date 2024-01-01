<?php

namespace App\Http\Controllers;

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
        ]);

        try {
            $script_data = ScriptData::find($request->script_id);

            if (!$script_data) {
                return response()->json(['message' => 'Invalid Data']);
            } else {
                $validate['status'] = 'Active';
            }

            $file = $request->file('file');
            $new_name = time() . '.' . $file->extension();
            $file->move(public_path('storage/scriptData'), $new_name);
            $validate['file'] = "storage/scriptData/$new_name";

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
}
