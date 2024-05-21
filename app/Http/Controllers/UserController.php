<?php

namespace App\Http\Controllers;

use App\Models\User;
use Google\Service\CloudSourceRepositories\Repo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Kreait\Firebase\Factory;
use Illuminate\Support\Str;
use Kreait\Laravel\Firebase\Facades\Firebase;

class UserController extends Controller
{
    /**
     * The function `createUser` in PHP validates and creates a new user, storing their information in
     * a database and also creating a user in Firebase Authentication.
     * 
     * @param Request request The `createUser` function you provided is a PHP function that handles the
     * creation of a new user. It takes a `Request` object as a parameter, which likely contains data
     * sent from a form or an API request.
     * 
     * @return The function `createUser` is returning a JSON response. If the user already exists, it
     * returns a message "User already exist!". If the user is successfully created, it returns a
     * message "User successfully added". If there is an error during the process, it returns an error
     * message with the details of the exception.
     */
    public function createUser(Request $request)
    {
        $validate = $request->validate([
            "name" => "required",
            "email" => "required",
            "password" => "required",
            "type" => "required",
            // 'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $user = User::whereEmail($request->email)->first();
            if ($user) {
                return response()->json(["message" => "User already exist!"]);
            } else {

                // if ($request->file('image')) {
                //     $imagePath = $request->file('image')->store('uploads', 'public');
                //     $image = $imagePath;
                // }
                $token = Str::random(60);
                $password = Hash::make($request->password);
                // $type = "Customer";
                $validate['last_name'] = $request->last_name;
                $validate['api_token'] = $token;
                $validate['password'] = $password;
                $validate['status'] = 'Active';
                User::create($validate);

                $factory = (new Factory)->withServiceAccount('../green-boom-cd923-firebase-adminsdk-dc132-0e4b577985.json')->withDatabaseUri('https://green-boom-cd923-default-rtdb.firebaseio.com/');
                $auth = $factory->createAuth();
                $userProperties = [
                    'email' => $request->email,
                    'emailVerified' => false,
                    'displayName' => $request->name,
                    'password'      => $request->password,
                ];
                $createdUser = $auth->createUser($userProperties);
                return response()->json(["message" => "User successfully added"]);
            }
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * This PHP function handles user login by checking the email, password, status, and type of the
     * user.
     * 
     * @param Request request The `login` function you provided is a method that handles user
     * authentication based on the provided email and password. It first attempts to find a user with
     * the given email address, who is active and has the type 'Admin'. If a user is found, it then
     * checks if the provided password matches the
     * 
     * @return If the user with the provided email is found, has an 'Active' status, and is of type
     * 'Admin', and the password provided matches the hashed password in the database, the function
     * will return a JSON response with the message 'Successfully logged in' and the user information.
     * If any of these conditions are not met, it will return a JSON response with the message 'Invalid
     * credentials'.
     */
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->whereStatus('Active')->whereType('Admin')->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 422);
        } else {
            return response()->json(['message' => 'Successfully logged in', 'user' => $user]);
        }
    }


    /**
     * This PHP function handles user login by verifying Firebase ID token, checking user status,
     * generating an API token, and returning appropriate responses.
     * 
     * @param Request request The `loginUser` function you provided seems to handle user authentication
     * using Firebase. It verifies the ID token provided in the request and then checks if the user
     * exists in the database based on their email.
     * 
     * @return The `loginUser` function returns a JSON response based on the conditions met during the
     * user login process. Here are the possible return scenarios:
     */
    public function loginUser(Request $request)
    {
        $auth = Firebase::auth();
        try {
            $uid = $request->token;
            $verifiedIdToken = $auth->verifyIdToken($uid);

            $uid = $verifiedIdToken->claims()->get('sub');

            $user = $auth->getUser($uid);

            $data = User::where('email', '=', $user->email)->first();

            if ($data) {

                if ($data->status != "Active") {
                    return response()->json(["message" => "Your Account is InActive By Admin"], 422);
                } else {

                    $token = Str::random(60);

                    $data->api_token = $token;
                    $data->save();

                    return response()->json(["message" => "Successfully Loged in", "detail" => $data, "token" => $token]);
                }
            } else {
                return response()->json(["message" => "you have to register first"], 422);
            }
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 422);
        }
    }


    /**
     * The function `registerUser` in PHP handles user registration by verifying the Firebase token,
     * creating a new user record, and sending a confirmation email.
     * 
     * @param Request request The `registerUser` function you provided seems to be a part of a Laravel
     * application and it handles the registration of a new user. Let's break down the parameters used
     * in this function:
     * 
     * @return The function `registerUser` is returning a JSON response with a message indicating the
     * registration status and a token, along with the user details if the registration is successful.
     * If there is an error during the registration process, it will return a JSON response with the
     * error message.
     */
    public function registerUser(Request $request)
    {
        $auth = Firebase::auth();
        try {
            $uid = $request->token;
            $verifiedIdToken = $auth->verifyIdToken($uid);

            $uid = $verifiedIdToken->claims()->get('sub');

            $user = $auth->getUser($uid);

            // $payment_link = "https://virtualrealitycreators.com/docdash-website/payment-process";
            // $code = "";
            $client = User::whereEmail($user->email)->first();
            if ($client)
                return response()->json(["message" => "Already Exist!"], 422);
            else
                $token = Str::random(60);
            $type = "User";
            $status = "Active";
            $profile_image = "profile/dummy.png";
            // if ($request->password == $request->confirm_password) {
            $users = new User([
                "name" => $request->name,
                "last_name" => $request->last_name,
                "email" => $request->email,
                "password" => Hash::make($request->password),
                "api_token" => $token,
                "profile_image" => $profile_image,
                "company_name" => $request->company_name,
                "type" => $type,
                "status" => $status,
            ]);

            // Mail::to($request->email)->send(new PaymentProcessLink($payment_link, $code));
            $users->save();

            $userDetails = User::find($users->id);
            // $factory = (new Factory)->withServiceAccount('../docdesh-cdb03-firebase-adminsdk-d1b0v-1582c88d5a.json')->withDatabaseUri('https://docdesh-cdb03-default-rtdb.firebaseio.com/');
            // $factory = (new Factory)->withServiceAccount(config('app.FIREBASE_CREDENTIALS'))->withDatabaseUri(config('app.FIREBASE_DATABASE_URL'));
            // // $database = $factory->createDatabase();
            // $auth = $factory->createAuth();

            // $userProperties = [
            //     'email' => $request->email,
            //     'emailVerified' => false,
            //     // 'phoneNumber' => '+15555550111',
            //     // 'password' => $doctor->password,
            //     'displayName' => $request->first_name,
            // ];

            // $createdUser = $auth->createUser($userProperties);

            return response()->json(["message" => " Your registration successfully done & mail sent", "token" => $token, 'detail' => $userDetails]);
            // } else {
            //     return response()->json(["message" => " password & confirm password doesn't match"], 422);
            // }
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 422);
        }
    }

    /**
     * The function `verify` retrieves the authenticated user and returns a JSON response containing
     * the user data or an error message if an exception occurs.
     * 
     * @param Request request The `Request ` parameter in the `verify` function is an instance
     * of the Illuminate\Http\Request class. This parameter allows you to access and interact with the
     * incoming HTTP request data, such as form input, headers, cookies, and files. It provides methods
     * for retrieving input, validating data,
     * 
     * @return The `verify` function is returning a JSON response. If the authentication is successful,
     * it returns the authenticated user information in the JSON response under the key "user". If an
     * error occurs during the process, it returns an error message in the JSON response under the key
     * "error" with a status code of 400.
     */
    public function verify(Request $request)
    {
        try {
            $user = auth()->user();
            return response()->json(["user" => $user]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }


    /**
     * The function retrieves and returns the details of a user with the specified ID in a JSON
     * response, handling any errors that may occur.
     * 
     * @param id The `id` parameter in the `userDetail` function is used to specify the unique
     * identifier of the user whose details you want to retrieve. This function attempts to find a user
     * record in the database based on the provided `id` and returns the user details in JSON format if
     * the user is found
     * 
     * @return The `userDetail` function returns a JSON response containing the user detail for the
     * user with the specified ``. If the user is found successfully, it returns the user detail in
     * the response. If an error occurs during the process, it returns a JSON response with an error
     * message and a status code of 400.
     */
    public function userDetail($id)
    {
        try {
            $user_detail = User::find($id);
            return response()->json(["user_detail" => $user_detail]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * The function updateUser in PHP updates user details including name, last name, company name,
     * password, and profile image, handling file uploads and error handling.
     * 
     * @param Request request The `updateUser` function is responsible for updating user details based
     * on the provided request data. Here's a breakdown of the code:
     * 
     * @return The updateUser function returns a JSON response. If the user is not found, it returns a
     * message "Unknown id". If there are any errors during the update process, it returns an error
     * message with the details of the exception. If the update is successful, it returns a message
     * "User details successfully updated" along with the updated user object in JSON format.
     */
    public function updateUser(Request $request)
    {
        // $validate = $request->validate([
        //     "name" => "required",
        //     "company_name" => "required",
        // ]);
        try {
            $user = User::find(auth()->user()->id);
            if (!$user) {
                return response()->json(["message" => "Unknown id"]);
            }
            // $validate['image'] = $request->file('image')->store('public/users');
            $user->name = $request->name;
            $user->last_name = $request->last_name;
            $user->company_name = $request->company_name;
            $user->password = Hash::make($request->new_password);
            if ($request->file('profile_image')) {
                $new_name = time() . '.' . $request->profile_image->extension();
                $path = $request->file('profile_image')->storeAs('users', $new_name, 's3');
                $user->profile_image = $path;
                // $request->profile_image->move(public_path('storage/users'), $new_name);
                // $user->profile_image = "storage/users/$new_name";
            }
            $user->save();
            return response()->json(["message" => "User details successfully updated", "user" => $user]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /////////////// For Admin

    /**
     * This PHP function retrieves a list of users excluding the authenticated user's email and returns
     * it as a JSON response.
     * 
     * @return The `users()` function is returning a JSON response containing a list of users fetched
     * from the database. The users are filtered based on the condition that their email is not equal
     * to the currently authenticated user's email. The list of users is ordered by their ID in
     * descending order. If an error occurs during the execution of the function, a JSON response with
     * the error message is returned with a status code of
     */
    public function users()
    {
        try {
            $users = User::where('email', '!=', auth()->user()->email)->orderBy('id', 'DESC')->get();
            // $users = User::all();
            return response()->json(["users" => $users]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * This PHP function deactivates a user by deleting their record from the database.
     * 
     * @param id The `deActive` function you provided is used to deactivate (block) a user by deleting
     * their record from the database. The function takes an `` parameter which is the unique
     * identifier of the user to be deactivated. This `` is used to find the user in the database
     * and then
     * 
     * @return The `deActive` function returns a JSON response with a message indicating whether the
     * user was successfully blocked or if there was an error. If the user with the specified ID is not
     * found, it returns a message stating "Invalid user". If the user is successfully deleted, it
     * returns a message stating "User blocked". If an error occurs during the process, it returns a
     * JSON response with the error message
     */
    public function deActive($id)
    {
        try {
            $user = User::find($id);
            if (!$user)
                return response()->json(["message" => "Invalid user"]);
            $user->delete();
            return response()->json(["message" => "User blocked"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * The function toggles the status of a user between "Active" and "Inactive" in a PHP application.
     * 
     * @param id The code you provided is a PHP function that toggles the status of a user between
     * "Active" and "Inactive" based on the user's current status. It first tries to find the user by
     * the provided ID, then updates the status accordingly and saves the changes.
     * 
     * @return The `status` function returns a JSON response with a message indicating whether the user
     * status has been changed successfully or not, along with the updated status value. If the user
     * with the provided ID is not found, it returns a message stating "Invalid user". If an error
     * occurs during the process, it returns a JSON response with the error message.
     */
    public function status($id)
    {
        // $request->validate([
        //     "user_id" => "required",
        // ]);
        try {
            $user = User::find($id);
            if (!$user)
                return response()->json(["message" => "Invalid user"]);
            if ($user->status == "Active") {
                $user->status = "InActive";
            } else {
                $user->status = "Active";
            }
            $user->save();
            return response()->json(["message" => "User status changed", "status" => $user->status], 200);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /**
     * The function resets a user's password by sending a password reset link to the provided email
     * address after validating it.
     * 
     * @param Request request The `Request ` parameter in the `reset` function is an instance
     * of the `Illuminate\Http\Request` class in Laravel. It represents an HTTP request and allows you
     * to access input data, files, cookies, and more from the request.
     * 
     * @return If the email address provided in the request is successfully validated and the password
     * reset link is sent, a JSON response with the message 'Password reset email sent!' is returned.
     * If the email address is not found in the Firebase authentication system, an error message 'Email
     * not found' is returned with validation errors.
     */
    public function reset(Request $request)
    {
        // Validate the email address
        $request->validate(['email' => 'required|email']);

        $auth = Firebase::auth();

        try {
            $auth->sendPasswordResetLink($request->input('email'));
            return response()->json(['message' => 'Password reset email sent!']);
        } catch (\Kreait\Firebase\Exception\Auth\EmailNotFound $e) {
            return back()->withErrors(['email' => 'Email not found']);
        }
    }
}
