<?php

namespace App\Http\Controllers;

use App\Models\Training;
use Illuminate\Http\Request;

class TrainingController extends Controller
{
    public function allTraining()
    {
        try {
            $all_training = Training::orderBy('id', 'DESC')->get();
            return response()->json(["all_training" => $all_training]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function addTraining(Request $request)
    {
        $validate = $request->validate([
            'title' => 'required'
        ]);
        try {
            $validate['status'] = 'Active';
            Training::create($validate);
            return response()->json(["message" => "Training successfully added"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

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

    public function TrainingStatus(Request $request)
    {
        $request->validate([
            "id" => "required",
        ]);
        try {
            $training = Training::find($request->id);
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

    public function customerTrainingList()
    {
        try {
            $training = Training::whereStatus('Active')->orderBy('id', 'DESC')->get();
            return response()->json(['training' => $training]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

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
