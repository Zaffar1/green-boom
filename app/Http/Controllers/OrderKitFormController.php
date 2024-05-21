<?php

namespace App\Http\Controllers;

use App\Mail\SendOrderKitDetail;
use App\Models\OrderKit;
use App\Models\OrderKitForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class OrderKitFormController extends Controller
{
    /**
     * The function `addOrderForm` processes and saves order form data, then sends an email
     * notification with the details.
     * 
     * @param Request request The `addOrderForm` function is used to add a new order form to the
     * database and send an email notification to the admin with the order details. Let me explain the
     * parameters used in this function:
     * 
     * @return The function `addOrderForm` is returning a JSON response. If the operation is
     * successful, it returns a JSON response with the message "Order kit successfully sent". If an
     * error occurs during the process, it returns a JSON response with the error message obtained from
     * the exception thrown.
     */
    public function addOrderForm(Request $request)
    {
        try {
            $admin_email = "alizafar@mailinator.com";
            $data = new OrderKitForm([
                "order_kit_id" => $request->order_kit_id,
                "first_name" => $request->first_name,
                "last_name" => $request->last_name,
                "email" => $request->email,
                "phone" => $request->phone,
                "company" => $request->company,
                "country" => $request->country,
                "state" => $request->state,
                "zip_code" => $request->zip_code,
                "city" => $request->city,
                "address" => $request->address,
                // "status" => "Active",
            ]);
            $data->save();
            Mail::to($admin_email)->send(new SendOrderKitDetail($data));
            return response()->json(["message" => "Order kit successfully sent"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * The function `orderKitData` retrieves order kit data along with related forms and returns it as
     * a JSON response, handling any errors with an appropriate message.
     * 
     * @return The `orderKitData` function is returning a JSON response. If the operation is
     * successful, it will return a JSON object with the key "kit_data" containing the data fetched
     * from the `OrderKitForm` model with its relationship to the `orderKit` model. If an error occurs
     * during the operation, it will return a JSON object with the key "error" containing the error
     * message retrieved
     */
    public function orderKitData()
    {
        try {
            $orderKitForms = OrderKitForm::with('orderKit')->get();
            return response()->json(["kit_data" => $orderKitForms]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * The function `deleteOrderKit` deletes an order kit record by its ID and returns a JSON response
     * indicating success or failure.
     * 
     * @param id The `deleteOrderKit` function is used to delete an order kit record based on the
     * provided `id` parameter. The `id` parameter represents the unique identifier of the order kit
     * that needs to be deleted from the database.
     * 
     * @return a JSON response. If the order kit with the specified ID is not found, it returns an
     * error message with status code 404. If the order kit is successfully deleted, it returns a
     * success message with status code 200. If an exception occurs during the deletion process, it
     * returns an error message with the exception message and status code 400.
     */
    public function deleteOrderKit($id)
    {
        try {
            $kit = OrderKitForm::find($id);
            if (!$kit) {
                return response()->json(["error" => "order kit not found"], 404);
            }
            $kit->delete();
            return response()->json(["message" => "Order kit data successfully deleted"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * This PHP function retrieves and returns the details of an order kit based on the provided ID.
     * 
     * @param id The `viewOrderKit` function takes an `` parameter, which is used to find an
     * `OrderKitForm` with the specified ID. This ID is then used to retrieve the details of the order
     * kit associated with that form.
     * 
     * @return The `viewOrderKit` function returns a JSON response containing the details of an order
     * kit. If the order kit is found, it returns the kit details and the kit itself. If the order kit
     * is not found, it returns an error message indicating that the order kit was not found. If an
     * exception occurs during the process, it returns an error message with the exception message.
     */
    public function viewOrderKit($id)
    {
        try {
            $kit = OrderKitForm::find($id);
            if (!$kit) {
                return response()->json(["error" => "order kit not found"], 404);
            }
            $kit_detail = OrderKit::find($kit->order_kit_id);
            return response()->json(["kit_detail" => $kit, "kit" => $kit_detail]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }
}
