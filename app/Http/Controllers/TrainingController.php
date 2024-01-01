<?php

namespace App\Http\Controllers;

use App\Models\MsdSheet;
use App\Models\PerfectSale;
use App\Models\PerfectSaleMedia;
use App\Models\Product;
use App\Models\Training;
use App\Models\TrainingMedia;
use App\Models\Video;
use Illuminate\Http\Request;

class TrainingController extends Controller
{

    public function hitRoute(Request $request)
    {
        $request->validate([
            'type' => 'required'
        ]);

        try {
            $data = [];
            $lowercaseType = strtolower($request->type);

            switch ($lowercaseType) {
                case 'training':
                    // Special handling for 'training' case
                    $categories = Training::whereStatus('Active')->orderBy('id', 'DESC')->get();
                    $category = Training::whereStatus('Active')->latest()->first();
                    $sub_cat = TrainingMedia::whereTrainingId($category->id)->whereStatus('Active')->get();
                    return response()->json(["cat" => $categories, "subCat" => $sub_cat]);

                case 'msdssheets':
                    $data = MsdSheet::whereStatus('Active')->orderBy('id', 'DESC')->get();
                    break;

                case 'videos':
                    $data = Video::whereStatus('Active')->orderBy('id', 'DESC')->get();
                    break;

                case 'salesptich':
                    $categories = PerfectSale::orderBy('id', 'DESC')->whereStatus('Active')->get();
                    $category = PerfectSale::whereStatus('Active')->latest()->first();
                    $sub_cat = PerfectSaleMedia::wherePerfectSaleId($category->id)->orderBy('id', 'DESC')->with('scriptMedia')->get();
                    return response()->json(["cat" => $categories, "subCat" => $sub_cat]);
                    break;

                case 'products':
                    $data = Product::whereStatus('Active')->orderBy('id', 'DESC')->get();
                    break;

                default:
                    return response()->json(["message" => "Invalid 'type' provided"], 400);
            }

            return response()->json(["subCat" => $data]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }


    /**
     * The function retrieves all training records from the database and returns them as a JSON
     * response.
     * 
     * @return a JSON response. If the try block is successful, it will return a JSON object containing
     * the "all_training" key with the value of the training data retrieved from the database. If there
     * is an error (caught by the catch block), it will return a JSON object with the "error" key and
     * the error message as the value.
     */
    public function allTraining()
    {
        try {
            $all_training = Training::orderBy('id', 'DESC')->get();
            return response()->json(["all_training" => $all_training]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * The addTraining function in PHP validates a request, sets the status to 'Active', creates a new
     * Training record, and returns a success message or an error message if an exception occurs.
     * 
     * @param Request request The  parameter is an instance of the Request class, which
     * represents an HTTP request made to the server. It contains information about the request, such
     * as the request method, headers, and request data.
     * 
     * @return a JSON response. If the training is successfully added, it will return a message
     * "Training successfully added". If there is an error, it will return an error message with a
     * status code of 400.
     */
    public function addTraining(Request $request)
    {
        $validate = $request->validate([
            'title' => 'required'
        ]);
        try {
            $validate['status'] = 'Active';
            Training::create($validate);
            return response()->json(["message" => "Training successfully added"], 200);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * The function `updateTraining` in PHP updates a training record based on the provided request
     * data.
     * 
     * @param Request request The  parameter is an instance of the Request class, which
     * represents an HTTP request made to the server. It contains information about the request, such
     * as the request method, headers, and input data. In this case, it is used to retrieve the input
     * data sent in the request.
     * 
     * @return a JSON response. If the validation fails, it will return a JSON response with an error
     * message. If the training is not found, it will return a JSON response with a message indicating
     * that the training is invalid. If the training is successfully updated, it will return a JSON
     * response with a message indicating that the training has been successfully updated. If an
     * exception occurs during the update process
     */
    public function updateTraining(Request $request)
    {
        $validate = $request->validate([
            'id' => 'required',
            'title' => 'required',
        ]);
        try {
            $training = Training::find($request->id);
            if (!$training)
                return response()->json(["message" => "Invalid training"]);
            $training->update($validate);
            return response()->json(["message" => "Training successfully updated"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * The function deletes a training record by its ID and returns a JSON response indicating whether
     * the deletion was successful or not.
     * 
     * @param id The "id" parameter is the unique identifier of the training that needs to be deleted.
     * 
     * @return a JSON response. If the training is found and successfully deleted, it will return a
     * JSON response with the message "Training deleted". If the training is not found, it will return
     * a JSON response with the message "Invalid training". If an error occurs during the deletion
     * process, it will return a JSON response with the error message.
     */
    public function deleteTraining($id)
    {
        try {
            $training = Training::find($id);
            if (!$training)
                return response()->json(["message" => "Invalid training"]);
            $training->delete();
            return response()->json(["message" => "Training deleted"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * The function "TrainingStatus" in PHP updates the status of a training based on the provided ID.
     * 
     * @param Request request The  parameter is an instance of the Request class, which
     * represents an HTTP request. It contains information about the request such as the request
     * method, headers, and input data. In this code snippet, the  parameter is used to
     * validate the input data and retrieve the "id" parameter from
     * 
     * @return a JSON response. If the training is found and the status is successfully changed, it
     * will return a JSON response with the message "Training status changed". If there is an error, it
     * will return a JSON response with the error message.
     */
    public function TrainingStatus($id)
    {
        // $request->validate([
        //     "id" => "required",
        // ]);
        try {
            $training = Training::find($id);
            if (!$training)
                return response()->json(["message" => "Invalid training"]);
            if ($training->status == "Active") {
                $training->status = "InActive";
            } else {
                $training->status = "Active";
            }
            $training->save();
            return response()->json(["message" => "Training status changed"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }


    /////////////////// For User

    /**
     * The function "customerTrainingList" retrieves a list of active trainings and returns it as a
     * JSON response, or returns an error message if an exception occurs.
     * 
     * @return a JSON response containing an array of training objects. The key for the array is
     * "training".
     */
    public function customerTrainingList()
    {
        try {
            $training = Training::whereStatus('Active')->orderBy('id', 'DESC')->get();
            return response()->json(['training' => $training]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * The function "trainingDetail" retrieves the details of a training based on the provided ID and
     * returns it as a JSON response.
     * 
     * @param Request request The  parameter is an instance of the Request class, which is used
     * to retrieve data from the HTTP request. It contains information such as the request method,
     * headers, and input data.
     * 
     * @return a JSON response. If the training with the specified ID is found, it will return the
     * training detail in the response. If there is an error, it will return an error message in the
     * response.
     */
    public function trainingDetail(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);
        try {
            $training = Training::find($request->id);
            return response()->json(['training_detail' => $training]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }
}
