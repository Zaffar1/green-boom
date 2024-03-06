<?php

namespace App\Http\Controllers;

use App\Models\PerfectSale;
use App\Models\PerfectSaleMedia;
use Illuminate\Http\Request;

class PerfectSaleController extends Controller
{
    /**
     * This PHP function retrieves all perfect sales data ordered by ID in descending order and returns
     * it as a JSON response, handling any errors that may occur.
     * 
     * @return The `allPerfectSales` function is returning a JSON response. If the retrieval of
     * PerfectSale records is successful, it returns a JSON response containing all the PerfectSale
     * records ordered by ID in descending order. If an error occurs during the retrieval process, it
     * returns a JSON response with an error message extracted from the caught exception.
     */
    public function allPerfectSales()
    {
        try {
            $all_perfect_sales = PerfectSale::orderBy('id', 'DESC')->get();
            return response()->json(['all_data' => $all_perfect_sales]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * This PHP function retrieves all active perfect sales data ordered by ID in descending order and
     * returns it as a JSON response.
     * 
     * @return The `customerAllPerfectSales` function is returning a JSON response containing all
     * perfect sales data that are ordered by ID in descending order and have a status of 'Active'. If
     * the retrieval is successful, it returns the JSON response with the data. If an error occurs
     * during the process, it returns a JSON response with an error message.
     */
    public function customerAllPerfectSales()
    {
        try {
            $all_perfect_sales = PerfectSale::orderBy('id', 'DESC')->whereStatus('Active')->get();
            return response()->json(['all_data' => $all_perfect_sales]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * The function `addPerfectSale` in PHP adds a new PerfectSale with a required title and sets its
     * status to Active, returning a success message or an error message if an exception occurs.
     * 
     * @param Request request The `addPerfectSale` function is a controller method that handles the
     * addition of a new PerfectSale entry. It expects a `Request` object as a parameter, which likely
     * contains the data needed to create a new PerfectSale entry.
     * 
     * @return If the validation passes and the PerfectSale is successfully added, the function will
     * return a JSON response with the message "PerfectSale successfully added" and a status code of
     * 200. If an error occurs during the process, it will return a JSON response with the error
     * message from the exception and a status code of 400.
     */
    public function addPerfectSale(Request $request)
    {
        $validate = $request->validate([
            'title' => 'required'
        ]);
        try {
            $validate['status'] = 'Active';
            PerfectSale::create($validate);
            return response()->json(["message" => "PerfectSale successfully added"], 200);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }


    /**
     * This PHP function updates a PerfectSale record based on the provided request data after
     * validating the required fields.
     * 
     * @param Request request The `updatePerfectSale` function is a PHP function that takes a `Request`
     * object as a parameter. The function is responsible for updating a PerfectSale model based on the
     * data provided in the request.
     * 
     * @return The `updatePerfectSale` function is returning a JSON response. If the validation fails,
     * it will return a JSON response with the validation errors. If the perfect sale is not found, it
     * will return a JSON response indicating that the perfect sale is invalid. If the update is
     * successful, it will return a JSON response indicating that the perfect sale was successfully
     * updated. If an exception occurs during the update process
     */
    public function updatePerfectSale(Request $request)
    {
        $validate = $request->validate([
            'id' => 'required',
            'title' => 'required',
        ]);
        try {
            $perfect_sale = PerfectSale::find($request->id);
            if (!$perfect_sale)
                return response()->json(["message" => "Invalid perfect sale"]);
            $perfect_sale->update($validate);
            return response()->json(["message" => "Perfect Sale successfully updated"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }


    /**
     * The function deletes a PerfectSale record by its ID and returns a JSON response indicating
     * success or failure.
     * 
     * @param id The `deletePerfectSale` function you provided is a PHP function that deletes a
     * PerfectSale record from the database based on the given ``.
     * 
     * @return The `deletePerfectSale` function is returning a JSON response. If the deletion is
     * successful, it returns a JSON response with the message "Perfect Sale deleted". If the perfect
     * sale with the given ID is not found, it returns a JSON response with the message "Invalid
     * perfect sale". If an error occurs during the deletion process, it returns a JSON response with
     * the error message.
     */
    public function deletePerfectSale($id)
    {
        try {
            $perfect_sale = PerfectSale::find($id);
            if (!$perfect_sale)
                return response()->json(["message" => "Invalid perfect sale"]);
            $perfect_sale->delete();
            return response()->json(["message" => "Perfect Sale deleted"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }


    /**
     * The function `PerfectSaleStatus` toggles the status of a PerfectSale entity between "Active" and
     * "InActive".
     * 
     * @param id The code you provided is a PHP function that toggles the status of a PerfectSale model
     * between "Active" and "Inactive" based on the provided ID. If the current status is "Active", it
     * will be changed to "Inactive", and vice versa.
     * 
     * @return The `PerfectSaleStatus` function is returning a JSON response. If the operation is
     * successful, it returns a JSON response with the message "Perfect Sale status changed". If there
     * is an error during the process, it returns a JSON response with the error message captured from
     * the exception.
     */
    public function PerfectSaleStatus($id)
    {
        // $request->validate([
        //     "id" => "required",
        // ]);
        try {
            $perfect_sale = PerfectSale::find($id);
            if (!$perfect_sale)
                return response()->json(["message" => "Invalid perfect sale"]);
            if ($perfect_sale->status == "Active") {
                $perfect_sale->status = "InActive";
            } else {
                $perfect_sale->status = "Active";
            }
            $perfect_sale->save();
            return response()->json(["message" => "Perfect Sale status changed"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }


    /**
     * This PHP function retrieves PerfectSaleMedia data based on a given PerfectSale ID and returns it
     * as a JSON response.
     * 
     * @param Request request The `perfectSaleMedia` function takes two parameters: `` and
     * ``.
     * @param id The `id` parameter in the `perfectSaleMedia` function is used to retrieve a specific
     * PerfectSale record from the database based on its unique identifier. This identifier is
     * typically passed as a parameter in the URL when making a request to this function. The function
     * then attempts to find the PerfectSale record
     * 
     * @return The code is returning a JSON response with the data of perfect sale media related to the
     * provided . If the perfect sale with the given  is not found, it returns a message
     * indicating that the perfect sale is invalid. If an error occurs during the process, it returns a
     * JSON response with the error message.
     */
    public function perfectSaleMedia(Request $request, $id)
    {
        // $request->validate([
        //     'perfect_sale_id' => 'required',
        // ]);
        try {
            $perfect_sale = PerfectSale::find($id);
            if (!$perfect_sale)
                return response()->json(["message" => "Invalid perfect sale"]);
            else
                $perfectSaleMedia = PerfectSaleMedia::wherePerfectSaleId($id)->orderBy('id', 'DESC')->with('scriptMedia')->get();
            // $perfectSaleMedia = PerfectSaleMedia::wherePerfectSaleId($id)->orderBy('id', 'DESC')->get();
            return response()->json(["data" => $perfectSaleMedia]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * The function retrieves Perfect Sale Media data based on the provided Perfect Sale ID.
     * 
     * @param Request request The `customerPerfectSaleMedia` function takes two parameters:
     * @param id The `id` parameter in the `customerPerfectSaleMedia` function is used to retrieve a
     * specific PerfectSale record based on its ID. The function then fetches related PerfectSaleMedia
     * records associated with the PerfectSale ID provided.
     * 
     * @return The code is returning a JSON response. If the PerfectSale with the given ID is found, it
     * retrieves the related PerfectSaleMedia records based on that ID, orders them by ID in descending
     * order, filters by 'Active' status, and includes the related PerfectSale information. The
     * response contains the data of the retrieved PerfectSaleMedia records. If there is an error
     * during the process, it returns a
     */
    public function customerPerfectSaleMedia(Request $request, $id)
    {
        // $request->validate([
        //     'perfect_sale_id' => 'required',
        // ]);
        try {
            $perfect_sale = PerfectSale::find($id);
            if (!$perfect_sale)
                return response()->json(["message" => "Invalid perfect sale"]);
            else
                $perfectSaleMedia = PerfectSaleMedia::wherePerfectSaleId($id)->orderBy('id', 'DESC')->whereStatus('Active')->with('perfectSales')->get();
            return response()->json(["data" => $perfectSaleMedia]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }
}
