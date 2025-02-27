<?php

namespace App\Http\Controllers;

use App\Models\PerfectSale;
use App\Models\PerfectSaleMedia;
use App\Models\Training;
use App\Models\TrainingMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TrainingMediaController extends Controller
{

    public function addTrainingMedia(Request $request)
    {
        $validate = $request->validate([
            'training_id' => 'required',
            'title' => 'required',
            'file' => 'required|mimes:pdf,mp4,mov,avi,doc,docx,ppt,pptx,xls,xlsx',
        ]);

        try {
            $training = Training::find($request->training_id);

            if (!$training) {
                return response()->json(['message' => 'Invalid Training']);
            } else {
                $validate['status'] = 'Active';
            }

            $file = $request->file('file');
            $new_name = time() . '.' . $file->extension();
            $path = $request->file('file')->storeAs('trainingMedia', $new_name, 's3');
            $validate['file'] = $path;
            // $file->move(public_path('storage/trainingMedia'), $new_name);
            // $validate['file'] = "storage/trainingMedia/$new_name";

            $file_type = $file->getClientOriginalExtension();

            $video_extension = ['mp4', 'avi', 'mov', 'wmv'];
            $pdf_extension = ['pdf'];
            $word_extension = ['doc', 'docx'];
            $ppt_extension = ['ppt', 'pptx'];
            $excel_extension = ['xls', 'xlsx'];

            if (in_array(strtolower($file_type), $video_extension)) {
                $validate['file_type'] = 'video';
                $new_name = time() . '.' . $request->thumbnail->extension();
                $thumbail = $request->file('thumbnail')->storeAs('trainingMedia/thumbnail', $new_name, 's3');
                $validate['thumbnail'] = $thumbail;
                // $request->thumbnail->move(public_path('storage/trainingMedia/thumbnail'), $new_name);
                // $validate['thumbnail'] = "storage/trainingMedia/thumbnail/$new_name";
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
            $validate['faq_text'] = $request->faq_text;
            TrainingMedia::create($validate);
            return response()->json(["message" => "Training file successfully added"], 200);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }


    // public function addTrainingMedia(Request $request)
    // {
    //     $validate = $request->validate([
    //         'training_id' => 'required',
    //         'title' => 'required',
    //         'file' => 'required|mimes:pdf,mp4,mov,avi,doc,docx,ppt,pptx,xls,xlsx',
    //     ]);
    //     try {
    //         $training = Training::find($request->training_id);
    //         if (!$training)
    //             return response()->json(['message' => 'Invalid Training']);
    //         else
    //             $validate['status'] = 'Active';
    //         $file = $request->file('file');
    //         // $validate['file'] = $request->file('file')->store('public/trainingMedia');
    //         $new_name = time() . '.' . $request->file->extension();
    //         $request->file->move(public_path('storage/trainingMedia'), $new_name);
    //         $validate['file'] = "storage/trainingMedia/$new_name";
    //         $file_type = $file->getClientOriginalExtension();
    //         $video_extension = ['mp4', 'avi', 'mov', 'wmv'];
    //         if (in_array(strtolower($file_type), $video_extension)) {
    //             $validate['file_type'] = 'video';
    //         }
    //         $pdf_extension = ['pdf'];
    //         if (in_array(strtolower($file_type), $pdf_extension)) {
    //             $validate['file_type'] = 'pdf';
    //         }
    //         TrainingMedia::create($validate);
    //         return response()->json(["message" => "Training file successfully added"], 200);
    //     } catch (\Throwable $th) {
    //         return response()->json(["error" => $th->getMessage()], 400);
    //     }
    // }

    public function trainingMedia(Request $request)
    {
        // $request->validate([
        //     'training_id' => 'required',
        // ]);
        try {
            if ($request->type == 'salespitch') {
                $training = PerfectSale::find($request->id);
                if (!$training)
                    return response()->json(["message" => "Invalid perfect sale"]);
                else
                    $trainingMedia = PerfectSaleMedia::wherePerfectSaleId($training->id)->orderBy('id', 'DESC')->with('scriptMedia')->get();
                return response()->json(["data" => $trainingMedia]);
            } else
                $training = Training::find($request->id);
            if (!$training)
                return response()->json(["message" => "Invalid training"]);
            else
                $trainingMedia = TrainingMedia::whereTrainingId($request->id)->orderBy('id', 'DESC')->whereStatus('Active')->get();
            return response()->json(["data" => $trainingMedia]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function deleteTrainingMedia($id)
    {
        try {
            $media = TrainingMedia::find($id);
            if (!$media)
                return response()->json(["message" => "Invalid Media"]);
            else
                $media->delete();
            // Use unlink for direct file deletion
            if (file_exists($media->file)) {
                unlink($media->file);
            }
            // Storage::delete($media->file);
            return response()->json(["message" => "Training media successfully deleted"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function updateMedia(Request $request)
    {
        $request->validate([
            // Add your validation rules as needed
        ]);

        try {
            $media = TrainingMedia::find($request->id);

            if (!$media) {
                return response()->json(["error" => "Training data not found"], 404);
            }

            $media->training_id = $request->training_id;
            $media->title = $request->title;

            if ($request->hasFile('file')) {
                // Delete the old file from storage
                if (file_exists($media->file)) {
                    unlink($media->file);
                }

                $file = $request->file('file');
                $new_name = time() . '.' . $file->extension();
                $path = $request->file('file')->storeAs('trainingMedia', $new_name, 's3');
                // $file->move(public_path('storage/trainingMedia'), $new_name);
                // $media->file = "storage/trainingMedia/$new_name";
                $media->file = $path;
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
            $thumb = $request->file('thumbnail')->storeAs('trainingMedia/thumbnail', $new_name, 's3');
            $media->thumbnail = $thumb;

            $media->save();
            return response()->json(["message" => "Training media successfully updated"], 200);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }



    // public function updateMedia(Request $request)
    // {
    //     $request->validate([
    //         // 'id' => 'required',
    //         // 'training_id' => 'required',
    //         // 'file' => 'required|mimes:pdf,mp4,mov,avi',
    //     ]);
    //     try {
    //         $media = TrainingMedia::find($request->id);
    //         if (!$media) {
    //             return response()->json(["error" => "Training data not found"], 404);
    //         }
    //         $media->training_id = $request->training_id;
    //         $media->title = $request->title;
    //         if ($request->hasFile('file')) {
    //             // Delete the old file from storage
    //             // Storage::delete($media->file);

    //             // Use unlink for direct file deletion
    //             if (file_exists($media->file)) {
    //                 unlink($media->file);
    //             }

    //             $file = $request->file('file');
    //             // $validate['file'] = $request->file('file')->store('public/trainingMedia');
    //             $new_name = time() . '.' . $request->file->extension();
    //             $request->file->move(public_path('storage/trainingMedia'), $new_name);
    //             $media->file = "storage/trainingMedia/$new_name";
    //             $file_type = $file->getClientOriginalExtension();
    //             $video_extension = ['mp4', 'avi', 'mov', 'wmv'];
    //             if (in_array(strtolower($file_type), $video_extension)) {
    //                 $media->file_type = 'video';
    //             }
    //             $pdf_extension = ['pdf'];
    //             if (in_array(strtolower($file_type), $pdf_extension)) {
    //                 $media->file_type = 'pdf';
    //             }
    //             // Store the new file
    //             // $media->file = $request->file('file')->store('public/trainingMedia');
    //         }
    //         $media->save();
    //         return response()->json(["message" => "Training media successfully updated"], 200);
    //     } catch (\Throwable $th) {
    //         return response()->json(["error" => $th->getMessage()], 400);
    //     }
    // }

    public function TrainingMediaStatus($id)
    {
        try {
            $training_media = TrainingMedia::find($id);
            if (!$training_media)
                return response()->json(["message" => "Invalid training media"]);
            if ($training_media->status == "Active") {
                $training_media->status = "InActive";
            } else {
                $training_media->status = "Active";
            }
            $training_media->save();
            return response()->json(["message" => "Training status changed"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }
}
