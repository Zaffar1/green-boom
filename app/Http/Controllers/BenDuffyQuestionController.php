<?php

namespace App\Http\Controllers;

use App\Models\BenDuffyQuestion;
use Illuminate\Http\Request;

class BenDuffyQuestionController extends Controller
{
    /**
     * The function retrieves all records from the BenDuffyQuestion model in descending order of ID and
     * returns them as a JSON response, handling any errors with a 400 status code.
     * 
     * @return An array containing all Ben Duffy questions in descending order by their IDs is being
     * returned as a JSON response. If an error occurs during the retrieval process, a JSON response
     * with an error message is returned with a status code of 400.
     */
    public function allBenDuffy()
    {
        try {
            $all_ben = BenDuffyQuestion::orderBy('id', 'DESC')->get();
            return response()->json(['all_ben_duffy' => $all_ben]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * The function retrieves all active Ben Duffy questions and returns them as a JSON response.
     * 
     * @return The `customerAllBenDuffy` function is returning a JSON response containing an array of
     * all Ben Duffy questions that have a status of 'Active', ordered by ID in descending order. If
     * the retrieval is successful, it will return the JSON response with the key 'all_ben_duffy'
     * containing the array of Ben Duffy questions. If an error occurs during the retrieval process, it
     * will return a
     */
    public function customerAllBenDuffy()
    {
        try {
            $all_ben = BenDuffyQuestion::whereStatus('Active')->orderBy('id', 'DESC')->get();
            return response()->json(['all_ben_duffy' => $all_ben]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * The function `addBenDuffy` in PHP adds a BenDuffyQuestion with required question and answer
     * fields and sets the status to "Active".
     * 
     * @param Request request The `addBenDuffy` function is a PHP function that takes a `Request`
     * object as a parameter. In Laravel, the `Request` object represents an HTTP request and contains
     * all the data sent with the request.
     * 
     * @return The function `addBenDuffy` is returning a JSON response. If the validation passes and
     * the BenDuffyQuestion is successfully created, it will return a JSON response with the message
     * "BenDuffyQuestion successfully added". If an error occurs during the creation process, it will
     * return a JSON response with the error message obtained from the caught exception.
     */
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


    /**
     * This PHP function updates a BenDuffyQuestion record based on the provided request data after
     * validating the required fields.
     * 
     * @param Request request The `updateBenDuffy` function is used to update a BenDuffyQuestion record
     * based on the provided request data. The function expects the following parameters in the
     * request:
     * 
     * @return The `updateBenDuffy` function is returning a JSON response. If the update operation is
     * successful, it will return a JSON response with the message "BenDuffyQuestion successfully
     * updated". If there is an error during the update operation, it will return a JSON response with
     * the error message captured from the exception thrown.
     */
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

    /**
     * This PHP function deletes a BenDuffyQuestion by its ID and returns a success message or an error
     * message if an exception occurs.
     * 
     * @param id The `deleteBenDuffy` function is used to delete a `BenDuffyQuestion` record from the
     * database based on the provided `` parameter.
     * 
     * @return The function `deleteBenDuffy` returns a JSON response. If the BenDuffyQuestion with the
     * given ID is found and successfully deleted, it returns a JSON response with the message
     * "BenDuffyQuestion successfully deleted". If the BenDuffyQuestion with the given ID is not found,
     * it returns a JSON response with the message "Invalid benDuffyQuestion". If an error occurs
     * during the
     */
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
