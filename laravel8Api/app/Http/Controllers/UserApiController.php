<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class UserApiController extends Controller
{
    public function showUser(Request $request , $id = null)
    {
        $header = $request->header('Authorization');
        if ($header == ''){
            $message = 'Authorization is required';
            return response()->json(['message'=>$message], 422);
        } else {
            if ($header == 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIwOTg3NjIiLCJuYW1lIjoiSEhISk5KTiIsImlhdCI6OTI4MzIzOTJ9.SO91yjmHUreCbGCMhO-Kdt91oFb3vmvuu6VSRLQfV_c'){
                if ($id == '') {
                    $users = User::get();
                    return response()->json(['users' => $users], 200);
                } else {
                    $users = User::find($id);
                    return response()->json(['users' => $users], 200);
                }
            } else {
                $message = 'Authorization done not match';
                return response()->json(['message'=>$message], 422);

            }
        }

       
    }

    public function addUser(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();

            $rules = [
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required',
            ];
            $customMessage = [
                'name.required' => 'name is required',
                'email.required' => 'email is required',
                'email.required' => 'email must be a valid email',
                'password.required' => 'password is required',
            ];

            $validator = Validator::make($data, $rules, $customMessage);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }
            // return $data;
            $user = new User();
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->password = bcrypt($data['password']);
            $user->save();
            $message = "User successfully Added";
            return response()->json(['message' => $message], 201);
        }
    }

    public function addMultipleUser(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();

            $rules = [
                'users.*.name' => 'required',
                'users.*.email' => 'required|email|unique:users',
                'users.*.password' => 'required',
            ];
            $customMessage = [
                'users.*.name.required' => 'name is required',
                'users.*.email.required' => 'email is required',
                'users.*.email.required' => 'email must be a valid email',
                'users.*.password.required' => 'password is required',
            ];

            $validator = Validator::make($data, $rules, $customMessage);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }
            // return $data;
            foreach ($data['users'] as $addUser) {
                $user = new User();
                $user->name = $addUser['name'];
                $user->email = $addUser['email'];
                $user->password = bcrypt($addUser['password']);
                $user->save();
                $message = 'User Successfully Added';
            }
            return response()->json(['message' => $message], 201);
        }
    }

    public function updateUserDetails(Request $request, $id)
    {
        if ($request->isMethod('put')) {
            $data = $request->all();

            $rules = [
                'name' => 'required',
                'password' => 'required',
            ];
            $customMessage = [
                'name.required' => 'name is required',
                'password.required' => 'password is required',
            ];

            $validator = Validator::make($data, $rules, $customMessage);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }
            // return $data;
            $user = User::findOrFail($id);
            $user->name = $data['name'];
            $user->password = bcrypt($data['password']);
            $user->save();
            $message = "User Successfully Updated";
            return response()->json(['message' => $message], 202);
        }
    }
    public function deleteUser($id)
    {
        User::findOrFail($id)->delete();
        $message = 'User Successfully Deleted';
        return response()->json(['message' => $message], 200);
    }
    public function deleteUserJson(Request $request)
    {
        if ($request->isMethod('delete')) {
            $data = $request->all();
            User::where('id', $data['id'])->delete();
            $message = 'User Successfully Deleted';
            return response()->json(['message' => $message], 200);
        }
    }
    public function deleteMultipleUser($ids)
    {
        $ids = explode(',', $ids);
        User::whereIn('id', $ids)->delete();
        $message = 'User Successfully Delete';
        return response()->json(['message' => $message], 200);
    }
    public function deleteMultipleUserJson(Request $request)
    {
        $data = $request->input('ids');
        $data = $request->all();

        // Assuming 'ids' is an array of user IDs to delete
        $ids = $data['ids'];

        // Check if the 'ids' array is not empty before attempting to delete
        if (!empty($ids)) {
            // Use a try-catch block to handle any potential errors
            try {
                User::whereIn('id', $ids)->delete();
                $message = 'Users Successfully Deleted';
                return response()->json(['message' => $message], 200);
            } catch (\Exception $e) {
                // Handle any exceptions, e.g., database errors
                $message = 'Error: ' . $e->getMessage();
                return response()->json(['message' => $message], 500);
            }
        } else {
            // Return a message if 'ids' array is empty
            $message = 'No user IDs provided for deletion.';
            return response()->json(['message' => $message], 400);
        }
    }
}
