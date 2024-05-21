<?php

namespace App\Http\Controllers;

use App\Models\SalesTip;
use Illuminate\Http\Request;

class SalesTipController extends Controller
{
    /**
     * Retrieve all sales tips from the database.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing all sales tips.
     */
    public function allSalesTips()
    {
        try {
            // Retrieve all sales tips from the database, ordered by ID in descending order
            $sales_tips = SalesTip::orderBy('id', 'DESC')->get();

            // Return a JSON response containing all sales tips
            return response()->json(["sales_tips_list" => $sales_tips]);
        } catch (\Throwable $th) {
            // If an error occurs during the retrieval process, return a JSON response with a 400 status containing the error message
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }


    ////////////// For Customer

    /**

     *Retrieve active sales tips for customers from the database.
     *This function fetches sales tips that are marked as "Active" from the database.
     *The retrieved sales tips are sorted in descending order based on their IDs.
     *If successful, it returns a JSON response containing the list of active sales tips.
     *If any error occurs during the retrieval process, it catches the exception and returns a JSON response with a 400 status containing the error message.
     *@return \Illuminate\Http\JsonResponse A JSON response containing active sales tips for customers.
     */
    public function customerSalesTips()
    {
        try {
            $sales_tips = SalesTip::whereStatus('Active')->orderBy('id', 'DESC')->get();
            return response()->json(["sales_tips_list" => $sales_tips]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    ///////////////// End 
    /**
     * Add a new sales tip to the database.
     *
     * This function adds a new sales tip to the database based on the provided request data.
     * The request data must include the title, description, and file of the sales tip.
     * The status of the sales tip is set to "Active" by default.
     * The file is stored in the configured S3 storage with a unique filename generated based on the current timestamp.
     * If successful, it returns a JSON response indicating that the sales tip has been successfully added.
     * If any error occurs during the addition process, it catches the exception and returns a JSON response with a 400 status containing the error message.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing the sales tip data.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the result of the sales tip addition.
     */
    public function addSalesTip(Request $request)
    {

        $validate = $request->validate([
            'title' => 'required',
            'description' => 'required',
            'file' => 'required',
        ]);
        try {
            $validate['status'] = 'Active';
            $new_name = time() . '.' . $request->file->extension();
            $path = $request->file('file')->storeAs('salesTips', $new_name, 's3');
            $validate['file'] = $path;
            // $request->file->move(public_path('storage/salesTips'), $new_name);
            // $validate['file'] = "storage/salesTips/$new_name";
            SalesTip::create($validate);
            return response()->json(["message" => "SalesTip successfully added"], 200);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * Update an existing sales tip in the database.
     *
     * This function updates an existing sales tip in the database based on the provided request data.
     * The request data must include the ID of the sales tip to be updated, along with the updated title, description, and optionally, the file of the sales tip.
     * If the specified sales tip is not found, it returns a JSON response with a 404 status indicating that the sales tip data was not found.
     * If the sales tip is found, it updates the title and description fields with the provided values.
     * If a new file is provided in the request, it deletes the previous file associated with the sales tip and uploads the new file to the configured S3 storage with a unique filename generated based on the current timestamp.
     * If successful, it returns a JSON response indicating that the sales tip has been successfully updated.
     * If any error occurs during the update process, it catches the exception and returns a JSON response with a 400 status containing the error message.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing the updated sales tip data.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the result of the sales tip update.
     */

    public function updateSalesTip(Request $request)
    {
        try {
            $sales_tip = SalesTip::find($request->id);
            if (!$sales_tip) {
                return response()->json(["error" => "SalesTip data not found"], 404);
            }
            $sales_tip->title = $request->title;
            $sales_tip->description = $request->description;
            if ($request->hasFile('file')) {

                // Use unlink for direct file deletion
                if (file_exists($sales_tip->file)) {
                    unlink($sales_tip->file);
                }
                $new_name = time() . '.' . $request->file->extension();
                $path = $request->file('file')->storeAs('salesTips', $new_name, 's3');
                $sales_tip->file = $path;
                // $request->file->move(public_path('storage/salesTips'), $new_name);
                // $sales_tip->file = "storage/salesTips/$new_name";
            }
            $sales_tip->save();
            return response()->json(["message" => "SalesTip successfully updated"], 200);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * Delete a sales tip from the database and associated file.
     *
     * This function deletes a sales tip from the database based on the provided ID.
     * If the specified sales tip is not found, it returns a JSON response with a 404 status indicating that the sales tip is invalid.
     * If the sales tip is found, it deletes the sales tip from the database and deletes the associated file from the storage.
     * If successful, it returns a JSON response indicating that the sales tip has been successfully deleted.
     * If any error occurs during the deletion process, it catches the exception and returns a JSON response with a 400 status containing the error message.
     *
     * @param int $id The ID of the sales tip to be deleted.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the result of the sales tip deletion.
     */
    public function deleteSalesTip($id)
    {
        try {
            $sales_tip = SalesTip::find($id);
            if (!$sales_tip)
                return response()->json(["message" => "Invalid SalesTip"]);
            else
                $sales_tip->delete();
            // Use unlink for direct file deletion
            if (file_exists($sales_tip->file)) {
                unlink($sales_tip->file);
            }
            // Storage::delete($sales_tip->file);
            return response()->json(["message" => "Sales_tip successfully deleted"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * Toggle the status of a sales tip between Active and Inactive.
     *
     * This function toggles the status of a sales tip between "Active" and "Inactive" based on the provided ID.
     * If the specified sales tip is not found, it returns a JSON response with a 404 status indicating that the sales tip is invalid.
     * If the sales tip is found, it toggles its status accordingly and saves the changes to the database.
     * If successful, it returns a JSON response indicating that the sales tip status has been successfully changed.
     * If any error occurs during the status toggle operation, it catches the exception and returns a JSON response with a 400 status containing the error message.
     *
     * @param int $id The ID of the sales tip whose status is to be toggled.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the result of the status toggle operation.
     */

    public function salesTipStatus($id)
    {
        try {
            $sales_tip = SalesTip::find($id);
            if (!$sales_tip)
                return response()->json(["message" => "Invalid salesTip"]);
            if ($sales_tip->status == "Active") {
                $sales_tip->status = "InActive";
            } else {
                $sales_tip->status = "Active";
            }
            $sales_tip->save();
            return response()->json(["message" => "salesTip status changed"], 200);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }
}
