<?php

namespace App\Http\Controllers;

use App\Models\PerfectSale;
use App\Models\PerfectSaleMedia;
use App\Models\ScriptData;
use Illuminate\Http\Request;

class PerfectSaleMediaController extends Controller
{
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
