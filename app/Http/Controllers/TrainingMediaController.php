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

    /**
     * Add training media to the database.
     *
     * This function adds training media to the database based on the provided request data.
     * The request data must include the training ID, title, file, and faq_text of the training media.
     * It validates the file type to ensure it is one of the allowed formats (pdf, mp4, mov, avi, doc, docx, ppt, pptx, xls, xlsx).
     * If the specified training is not found, it returns a JSON response indicating that the training is invalid.
     * If the training is found, it associates the training media with the training ID and sets the status to "Active".
     * It uploads the file to the configured S3 storage with a unique filename generated based on the current timestamp.
     * It determines the type of the file (video, pdf, word, ppt, excel, or other) based on its extension.
     * If successful, it returns a JSON response indicating that the training media has been successfully added.
     * If any error occurs during the addition process, it catches the exception and returns a JSON response with a 400 status containing the error message.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing the training media data.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the result of the training media addition.
     */

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

    /**
     * Retrieve training media based on the provided type and ID.
     *
     * This function retrieves training media based on the provided type and ID.
     * If the type is 'salespitch', it retrieves perfect sale media associated with the specified perfect sale ID.
     * If the type is not 'salespitch', it retrieves training media associated with the specified training ID.
     * If the specified perfect sale or training is not found, it returns a JSON response indicating that the data is invalid.
     * If successful, it returns a JSON response containing the retrieved training media.
     * If any error occurs during the process, it catches the exception and returns a JSON response with a 400 status containing the error message.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing the type and ID parameters.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the retrieved training media.
     */

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

    /**
     * Delete training media from the database and storage.
     *
     * This function deletes training media from the database and storage based on the provided ID.
     * If the specified training media is not found, it returns a JSON response indicating that the media is invalid.
     * If successful, it returns a JSON response indicating that the training media has been successfully deleted.
     * If any error occurs during the deletion process, it catches the exception and returns a JSON response with a 400 status containing the error message.
     *
     * @param int $id The ID of the training media to be deleted.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the result of the training media deletion.
     */

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

    /**
     * Update training media in the database and storage.
     *
     * This function updates training media in the database and storage based on the provided request data.
     * The request data must include the ID of the training media to be updated, along with any fields that need to be modified.
     * It retrieves the existing training media based on the provided ID.
     * If the specified training media is not found, it returns a JSON response indicating that the media is not found.
     * If the specified training media is found, it updates its fields with the provided data.
     * If a new file is uploaded, it deletes the old file from storage and uploads the new file to the configured S3 storage with a unique filename generated based on the current timestamp.
     * It determines the type of the new file and updates the file_type field accordingly.
     * If successful, it returns a JSON response indicating that the training media has been successfully updated.
     * If any error occurs during the update process, it catches the exception and returns a JSON response with a 400 status containing the error message.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing the training media data to be updated.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the result of the training media update.
     */

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

    /**
     * Change the status of training media.
     *
     * This function changes the status of training media based on the provided ID.
     * It retrieves the training media with the specified ID.
     * If the specified training media is not found, it returns a JSON response indicating that the media is invalid.
     * If the status of the training media is "Active", it changes it to "InActive", and vice versa.
     * It then saves the changes to the database.
     * If successful, it returns a JSON response indicating that the training media status has been changed.
     * If any error occurs during the process, it catches the exception and returns a JSON response with a 400 status containing the error message.
     *
     * @param int $id The ID of the training media whose status needs to be changed.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the result of the status change operation.
     */

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
