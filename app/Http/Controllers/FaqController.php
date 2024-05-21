<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    /**
     * The function retrieves all FAQs from the database and returns them as a JSON response.
     * 
     * @return An array containing all FAQs in descending order by their IDs is being returned.
     */
    public function allFaqs()
    {
        try {
            $faqs = Faq::orderBy('id', 'DESC')->get();
            return response()->json(["all_faqs" => $faqs]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * The function `customerAllFaqs` retrieves all active FAQs ordered by ID in descending order and
     * returns them as a JSON response, handling any errors that may occur.
     * 
     * @return An array containing all active FAQs in descending order of their IDs is being returned
     * in JSON format.
     */
    public function customerAllFaqs()
    {
        try {
            $faqs = Faq::whereStatus('Active')->orderBy('id', 'DESC')->get();
            return response()->json(["all_faqs" => $faqs]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * The function `addFaq` in PHP adds a new FAQ entry with a required `faq_text` field and sets the
     * status to "Active".
     * 
     * @param Request request The `addFaq` function is a PHP function that receives a `Request` object
     * as a parameter. The `Request` object typically contains the data sent to the server in an HTTP
     * request.
     * 
     * @return The `addFaq` function is returning a JSON response. If the Faq creation is successful,
     * it will return a JSON response with a message indicating that the Faq was successfully added. If
     * an error occurs during the process, it will return a JSON response with an error message
     * extracted from the caught exception.
     */
    public function addFaq(Request $request)
    {
        $validate = $request->validate([
            // 'question' => 'required',
            // 'answer' => 'required',
            'faq_text' => 'required',
        ]);
        try {
            $validate['status'] =  "Active";
            Faq::create($validate);
            return response()->json(["message" => "Faq successfully added"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * The function `updateFaq` updates a FAQ entry in a database based on the provided request data.
     * 
     * @param Request request The `updateFaq` function is designed to update a FAQ entry in a database
     * based on the provided request data. Here is a breakdown of the function:
     * 
     * @return The `updateFaq` function is returning a JSON response. If the update operation is
     * successful, it returns a JSON response with the message "Faq successfully updated". If an error
     * occurs during the update operation, it returns a JSON response with the error message obtained
     * from the exception thrown.
     */
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

    /**
     * This PHP function deletes a FAQ entry by its ID and returns a JSON response indicating success
     * or failure.
     * 
     * @param id The `deleteFaq` function is used to delete a FAQ entry from the database based on the
     * provided `id` parameter. The `id` parameter represents the unique identifier of the FAQ entry
     * that needs to be deleted.
     * 
     * @return The function `deleteFaq()` returns a JSON response. If the FAQ with the specified ID
     * is found and successfully deleted, it returns a JSON response with the message "Faq successfully
     * deleted". If the FAQ with the specified ID is not found (i.e., `` is null), it returns a
     * JSON response with the message "Invalid faq". If an error occurs during the
     */
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
