<?php

namespace App\Http\Controllers;

use App\Models\MsdSheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MsdSheetController extends Controller
{
    public function allMsdsSheets()
    {
        try {
            $all_msd = MsdSheet::orderBy('id', 'DESC')->get();
            return response()->json(['all_msds' => $all_msd]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function customerAllMsdsSheets()
    {
        try {
            $all_msd = MsdSheet::whereStatus('Active')->orderBy('id', 'DESC')->get();
            return response()->json(['all_msds' => $all_msd]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

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
            $request->file->move(public_path('storage/msdSheets'), $new_name);
            $validate['file'] = "storage/msdSheets/$new_name";

            $file_type = strtolower($request->file->getClientOriginalExtension());
            $pdf_extension = ['pdf'];
            $word_extension = ['doc', 'docx'];
            $ppt_extension = ['ppt', 'pptx'];
            $excel_extension = ['xls', 'xlsx'];

            if ($request->has('image')) {
                $new_name = time() . '.' . $request->image->extension();
                $request->image->move(public_path('storage/msdSheets/images'), $new_name);
                $validate['image'] = "storage/msdSheets/images/$new_name";
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
                $request->file->move(public_path('storage/msdSheets'), $new_name);

                // Use unlink for direct file deletion
                if (file_exists($msd->file)) {
                    unlink($msd->file);
                }

                $msd->file = "storage/msdSheets/$new_name";

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
                // $new_name = time() . '.' . $request->image->extension();
                // $request->image->move(public_path('storage/msdSheets/images'), $new_name);
                // $msd->image = "storage/msdSheets/images/$new_name";
                $path = $request->file('image')->store('msdSheet/images', 's3');
                $path = "https://vrc-bucket.s3.us-east-2.amazonaws.com/$path";
                $msd->image = $path;
            }

            $msd->save();
            return response()->json(['message' => 'Msd sheet successfully updated'], 200);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

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
