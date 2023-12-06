<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function allFaqs()
    {
        try {
            $faqs = Faq::orderBy('id', 'DESC')->get();
            return response()->json(["all_faqs" => $faqs]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function customerAllFaqs()
    {
        try {
            $faqs = Faq::whereStatus('Active')->orderBy('id', 'DESC')->get();
            return response()->json(["all_faqs" => $faqs]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function addFaq(Request $request)
    {
        $validate = $request->validate([
            'question' => 'required',
            'answer' => 'required',
        ]);
        try {
            $validate['status'] =  "Active";
            Faq::create($validate);
            return response()->json(["message" => "Faq successfully added"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function updateFaq(Request $request)
    {
        $validate = $request->validate([
            'id' => 'required',
            'question' => 'required',
            'answer' => 'required',
        ]);
        try {
            $faq = Faq::find($request->id)->update([
                'question' => $request->question,
                'answer' => $request->answer
            ]);
            return response()->json(["message" => "Faq successfully updated"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function deleteFaq($id)
    {
        try {
            $faq = Faq::find($id);
            if (!$faq)
                return response()->json(["message" => "Invalid faq"]);
            $faq->delete();
            return response()->json(["message" => "Faq successfully deleted"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }
}
