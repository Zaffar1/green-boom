<?php

namespace App\Http\Controllers;

use App\Models\BenDuffyQuestion;
use Illuminate\Http\Request;

class BenDuffyQuestionController extends Controller
{
    public function allBenDuffy()
    {
        try {
            $all_ben = BenDuffyQuestion::orderBy('id', 'DESC')->get();
            return response()->json(['all_ben_duffy' => $all_ben]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }


    public function addBenDuffy(Request $request)
    {
        $validate = $request->validate([
            'question' => 'required',
            'answer' => 'required',
        ]);
        try {
            $validate['status'] =  "Active";
            BenDuffyQuestion::create($validate);
            return response()->json(["message" => "BenDuffyQuestion successfully added"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }


    public function updateBenDuffy(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'question' => 'required',
            'answer' => 'required',
        ]);
        try {
            $benDuffyCheck = BenDuffyQuestion::find($request->id);
            if (!$benDuffyCheck)
                return response()->json(['message' => 'Invalid BenDuffy']);
            $benDuffy = BenDuffyQuestion::find($request->id)->update([
                'question' => $request->question,
                'answer' => $request->answer
            ]);
            return response()->json(["message" => "BenDuffyQuestion successfully updated"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function deleteBenDuffy($id)
    {
        try {
            $benDuffy = BenDuffyQuestion::find($id);
            if (!$benDuffy)
                return response()->json(["message" => "Invalid benDuffyQuestion"]);
            $benDuffy->delete();
            return response()->json(["message" => "BenDuffyQuestion successfully deleted"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }
}
