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

    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 422);
        } else {
            return response()->json(['message' => 'Successfully logged in', 'user' => $user]);
        }
    }


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
            // $type = "Parent";
            $status = "Active";
            // if ($request->password == $request->confirm_password) {
            $users = new User([
                "name" => $request->name,
                "email" => $request->email,
                "password" => Hash::make($request->password),
                "api_token" => $token,
                "company_name" => $request->company_name,
                // "type" => $request->type,
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

    public function verify(Request $request)
    {
        try {
            $user = auth()->user();
            return response()->json(["user" => $user]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }


    public function userDetail($id)
    {
        try {
            $user_detail = User::find($id);
            return response()->json(["user_detail" => $user_detail]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function updateUser(Request $request)
    {
        // $validate = $request->validate([
        //     "name" => "required",
        //     "company_name" => "required",
        // ]);
        try {
            $user = User::find(auth()->user()->id);
            // $validate['image'] = $request->file('image')->store('public/users');
            $user->name = $request->name;
            $user->company_name = $request->company_name;
            if ($request->file('profile_image')) {
                $new_name = time() . '.' . $request->profile_image->extension();
                $request->profile_image->move(public_path('storage/users'), $new_name);
                $user->profile_image = "storage/users/$new_name";
            }
            $user->save();
            return response()->json(["message" => "User details successfully updated", "user" => $user]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    /////////////// For Admin

    public function users()
    {
        try {
            $users = User::where('email', '!=', auth()->user()->email)->get();
            // $users = User::all();
            return response()->json(["users" => $users]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

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
