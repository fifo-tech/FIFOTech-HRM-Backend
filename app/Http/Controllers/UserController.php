<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\EmployeeProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    // Sign Up API
    public function signUp(Request $request)
    {
        try {
            // Validate the incoming request
            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:8|confirmed', // Ensure password confirmation
            ]);

            // Create a new user
            $user = new User();
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password); // Hash the password
            $user->active_status = 1;
            $user->role_id = 1;
            if($user->save()) {
                $employee = new Employee();
                $employee->user_id = $user->id;
                $employee->first_name = $request->first_name;
                $employee->last_name = $request->last_name;
                $employee->email = $request->email;
                $employee->supervisor_id = $user->id;
                $employee->save();
            }


            // Generate a Sanctum token
            $token = $user->createToken('api')->plainTextToken;

            // Return success response with the user and token
            return $this->response(true,'Sign Up Successful',$user,201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return $this->response(false,$e->getMessage(),[],500);

        } catch (\Exception $e) {
            // Handle all other exceptions
            return $this->response(false,$e->getMessage(),[],500);
        }

    }




//    public function createEmployeeZZZZZZZZ(Request $request)
//    {
//        try {
//            // Validate the incoming request
//            $request->validate([
//                'first_name' => 'required|string|max:255',
//                'last_name' => 'required|string|max:255',
//                'email' => 'required|email|unique:users,email|max:255',
//                'phone_num' => 'nullable|string|max:20',
//                'gender' => 'nullable|string|in:male,female,other',
//                'dept_id' => 'required|exists:departments,id',
//                'designation_id' => 'required|exists:designations,id',
//                'office_shift' => 'nullable|string|max:255',
//                'basic_salary' => 'nullable|numeric',
//                'hourly_rate' => 'nullable|numeric',
//                'payslip_type' => 'nullable|string|max:255',
//                'date_of_birth' => 'nullable|date',
//                'marital_status' => 'nullable|string|max:50',
//                'district' => 'nullable|string|max:255',
//                'city' => 'nullable|string|max:255',
//                'zip_code' => 'nullable|string|max:10',
//                'religion' => 'nullable|string|max:100',
//                'blood_group' => 'nullable|string|max:5',
//                'nationality' => 'nullable|string|max:100',
//                'present_address' => 'nullable|string',
//                'permanent_address' => 'nullable|string',
//                'bio' => 'nullable|string',
//                'experience' => 'nullable|integer|min:0',
//                'facebook' => 'nullable|url',
//                'linkedin' => 'nullable|url',
//            ]);
//
//            // Create a new user
//            $user = new User();
//            $user->first_name = $request->first_name;
//            $user->last_name = $request->last_name;
//            $user->emp_id = $request->emp_id ?? 'E' . now()->timestamp; // Auto-generate EMP ID if not provided
//            $user->phone_num = $request->phone_num ?? null;
//            $user->gender = $request->gender ?? null;
//            $user->dept_id = $request->dept_id;
//            $user->designation_id = $request->designation_id;
//            $user->email = $request->email;
//            $user->password = Hash::make('12345678'); // Set a default password
//            $user->role_id = 3; // Role for employee
//            $user->active_status = 1;
//
//            if ($user->save()) {
//                // Create an employee profile
//                $profile = new EmployeeProfile();
//                $profile->user_id = $user->id;
//                $profile->first_name = $request->first_name;
//                $profile->last_name = $request->last_name;
//                $profile->emp_id = $user->emp_id;
//                $profile->phone_num = $request->phone_num ?? null;
//                $profile->gender = $request->gender ?? null;
//                $profile->dept_id = $request->dept_id;
//                $profile->designation_id = $request->designation_id;
//                $profile->email = $request->email;
//                $profile->office_shift = $request->office_shift ?? null;
//                $profile->basic_salary = $request->basic_salary ?? null;
//                $profile->hourly_rate = $request->hourly_rate ?? null;
//                $profile->payslip_type = $request->payslip_type ?? null;
//                $profile->date_of_birth = $request->date_of_birth ?? null;
//                $profile->marital_status = $request->marital_status ?? null;
//                $profile->district = $request->district ?? null;
//                $profile->city = $request->city ?? null;
//                $profile->zip_code = $request->zip_code ?? null;
//                $profile->religion = $request->religion ?? null;
//                $profile->blood_group = $request->blood_group ?? null;
//                $profile->nationality = $request->nationality ?? null;
//                $profile->present_address = $request->present_address ?? null;
//                $profile->permanent_address = $request->permanent_address ?? null;
//                $profile->bio = $request->bio ?? null;
//                $profile->experience = $request->experience ?? null;
//                $profile->facebook = $request->facebook ?? null;
//                $profile->linkedin = $request->linkedin ?? null;
//                $profile->save();
//
//                // Return success response
//                return response()->json([
//                    'message' => 'Employee account created successfully',
//                    'user' => $user,
//                    'employee_profile' => $profile,
//                ], 201);
//            }
//
//            return response()->json(['error' => 'Failed to create employee account'], 500);
//        } catch (ValidationException $e) {
//            // Handle validation errors
//            return response()->json(['error' => 'Validation Error', 'details' => $e->errors()], 422);
//        } catch (\Exception $e) {
//            // Handle all other exceptions
//            return response()->json(['error' => 'Something went wrong', 'message' => $e->getMessage()], 500);
//        }
//    }











    // Login API
    public function login(Request $request)
    {
        try {
            // Validate the incoming request
            $request->validate([
                'email' => 'required|email|max:255',
                'password' => 'required|string|min:8',
            ]);

            // Attempt to find the user by email
            $user = User::where('email', $request->email)->first();

            // Check if the user exists
            if (!$user || !Hash::check($request->password, $user->password)) {
                return $this->response(false, 'Email or password is invalid', [], 401);
            }

            // Generate a Sanctum token
            $token = $user->createToken('api')->plainTextToken;
            $user->token = $token;

            // Return success response with the user and token
            return $this->response(true, 'User logged in successfully', $user, 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return $this->response(false, 'Validation error', $e->errors(), 422);

        } catch (\Exception $e) {
            // Handle unexpected exceptions
            return $this->response(false, 'An error occurred during login', $e->getMessage(), 500);
        }
    }

    // Logout API
    public function logout(Request $request)
    {
        try {
            // Revoke the current user's token
            $request->user()->currentAccessToken()->delete();

            return $this->response(true, 'User logged out successfully', [], 200);

        } catch (\Exception $e) {
            return $this->response(false, 'An error occurred during logout', $e->getMessage(), 500);
        }
    }



}
