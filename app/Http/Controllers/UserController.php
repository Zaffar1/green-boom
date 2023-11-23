<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Kreait\Firebase\Factory;
use Illuminate\Support\Str;

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


    public function verify()
    {
        try {
            $user = User::find(auth()->user()->id);
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
        $validate = $request->validate([
            "name" => "required",
            "last_name" => "required",
        ]);
        try {
            $user = User::find(auth()->user()->id);
            $user->update($validate);
            return response()->json(["message" => "User successfully updated"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }


    /////////////// For Admin

    public function users()
    {
        try {
            $users = User::all();
            return response()->json(["users" => $users]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function deActive(Request $request)
    {
        $request->validate([
            "user_id" => "required",
        ]);
        try {
            $user = User::find($request->user_id);
            $user->delete();
            return response()->json(["message" => "User blocked"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function status(Request $request)
    {
        $request->validate([
            "user_id" => "required",
        ]);
        try {
            $user = User::find($request->user_id);
            if ($user->status == "Active") {
                $user->status = "DeActive";
            } else {
                $user->status = "Active";
            }
            $user->save();
            return response()->json(["message" => "User status changed"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }
}
