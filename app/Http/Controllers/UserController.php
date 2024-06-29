<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Helpers\Common;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * User Index
     * @param Request $request
     * @param Response $response
     */
    public function index(Request $request) {
        try {
            $response = [];
            // Validation given Data's
            $validator = array(
                'user'      => ['array', 'required'],
                'client'    => ['array', 'required'],
                'page'      => ['integer', 'required'],
                'limit'     => ['integer', 'required'],
                'sort'      => ['array', 'nullable'],
                'search'    => ['array', 'nullable'],
            );
            $validator = Common::validator($request, $validator);
            if ($validator && $validator->fails()) {
                return ApiResponse::send(203, null, $validator->errors()->first(), true);
            }
            $response = User::index($request);
            if ($response) {
                return ApiResponse::send(200, $response, 'Users list');
            }
            return ApiResponse::send(203);
        } catch(Exception $e) {
            Log::info($e);
            return ApiResponse::send(500);
        }
    }

    /**
     * User Form Data
     * @param Request $request
     * @param Response $response
     */
    public function formData(Request $request) {
        try {
            $response = [];
            // Validation given Data's
            $validator = array(
                'user'      => ['array', 'required'],
                'client'    => ['array', 'required'],
                'id'        => ['integer', 'nullable'],
            );
            $validator = Common::validator($request, $validator);
            if ($validator && $validator->fails()) {
                return ApiResponse::send(203, null, $validator->errors()->first(), true);
            }
            $response['data'] = User::first($request);
            if ($response) {
                return ApiResponse::send(200, $response, 'User form data');
            }
            return ApiResponse::send(203);
        } catch(Exception $e) {
            Log::info($e);
            return ApiResponse::send(500);
        }
    }

    /**
     * User Get List
     * @param Request $request
     * @param Response $response
     */
    public function getList(Request $request) {
        try {
            $response = [];
            // Validation given Data's
            $validator = array(
                'user'      => ['array', 'required'],
                'client'    => ['array', 'required'],
            );
            $validator = Common::validator($request, $validator);
            if ($validator && $validator->fails()) {
                return ApiResponse::send(203, null, $validator->errors()->first(), true);
            }
            $response = User::get($request);
            if ($response) {
                return ApiResponse::send(200, $response, 'Users list');
            }
            return ApiResponse::send(203);
        } catch(Exception $e) {
            Log::info($e);
            return ApiResponse::send(500);
        }
    }


    /**
     * User Store
     * @param Request $request
     * @param Response $response
     */
    public function store(Request $request) {
        try {
            $response = [];
            // Validation given Data's
            $validator = array(
                'user'      => ['array', 'required'],
                'client'    => ['array', 'required'],
                'id'        => ['integer', 'nullable'],
                'name'      => ['string', 'nullable'],
                'email'     => ['string', 'unique:users,email,'.$request->id],
                'password'  => ['string', 'nullable'],
                'role_id'   => ['integer', 'nullable'],
                'status'    => ['boolean', 'nullable']
            );
            $validator = Common::validator($request, $validator);
            if ($validator && $validator->fails()) {
                return ApiResponse::send(203, null, $validator->errors()->first(), true);
            }
            $response = User::store($request);
            if ($response) {
                return ApiResponse::send(201, $response, 'User stored successfully');
            }
            return ApiResponse::send(203);
        } catch(Exception $e) {
            Log::info($e);
            return ApiResponse::send(500);
        }
    }

    /**
     * Change Password
     * @param Request $request
     * @return Response $response
     */
    public function changePassword(Request $request) {
        $response = null;
        try {
            // Validation given Data's
            $validator = array(
                'user'      => ['array', 'required'],
                'client'    => ['array', 'required'],
                'id'           => ['integer', 'required'],
                'old_password' => ['string', 'required'],
                'new_password' => ['string', 'required']
            );
            $validator = Common::validator($request, $validator);
            if ($validator && $validator->fails()) {
                return ApiResponse::send(203, null, $validator->errors()->first(), true);
            }
            $user = User::getUserById($request->id);
            if (!@$user) {
                return ApiResponse::send(203, null, "Invalid user");
            }
            if ($request->old_password == $request->new_password) {
                return ApiResponse::send(203, null, "Old and new password cannot be same");
            }
            if (!Common::hashCheck($request->old_password, $user->password)) {
                return ApiResponse::send(203, null, "Old password is wrong");
            }
            if (Common::hashCheck($request->new_password, $user->password)) {
                return ApiResponse::send(203, null, "Cannot use the current password");
            }
            $response = User::changePassword($request);
            if ($response) {
                User::logoutAllDevices($request);
                return ApiResponse::send(200, $response, 'User password changed successfully');
            } else {
                return ApiResponse::send(203);
            }
        } catch(Exception $e) { 
            Log::info($e);
            return ApiResponse::send(500);
        }
    }
}
