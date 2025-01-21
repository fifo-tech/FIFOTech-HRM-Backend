<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Department;
use App\Models\Designation;

class EmployeeController extends Controller
{
    public function createEmployee(Request $request)
    {
        try {
            // Validate the incoming request
            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'phone_num' => 'nullable|string|max:15',
                'gender' => 'nullable|string|max:10',
                'dept_id' => 'nullable|exists:departments,id',
                'designation_id' => 'nullable|exists:designations,id',
                'emp_id' => 'nullable|string|max:50',
            ]);

            // Determine the emp_id
            $empId = $request->emp_id;
            if (!$empId) {
                $lastEmployee = Employee::orderBy('emp_id', 'desc')->first();
                $lastEmpNumber = $lastEmployee ? (int) substr($lastEmployee->emp_id, strrpos($lastEmployee->emp_id, '-') + 1) : 0;
                $currentYear = date('Y');
                $nextEmpNumber = $lastEmpNumber + 1;
                $empId = "WTH-ID-$currentYear-$nextEmpNumber";
            }

            // Create a new user
            $user = new User();
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->password = Hash::make("password"); // Hash the password
            $user->active_status = 1;
            $user->role_id = 3; // Default to 'Employee' role

            // Handle profile picture upload if exists
            if ($request->hasFile('profile_picture')) {
                // Store the image and get its path
                $profilePicturePath = $request->file('profile_picture')->store('profile_pictures', 'public');
                // Save the path in the database
                $user->profile_photo_path = $profilePicturePath;
            }

            if ($user->save()) {
                // Create employee associated with the user
                $employee = new Employee();
                $employee->user_id = $user->id;
                $employee->first_name = $request->first_name;
                $employee->last_name = $request->last_name;
                $employee->email = $request->email;
                $employee->supervisor_id = $request->supervisor_id ?: $user->id; // Assign supervisor if available, otherwise self
                $employee->emp_id = $empId;
                $employee->phone_num = $request->phone_num;
                $employee->gender = $request->gender;
                $employee->dept_id = $request->dept_id;
                $employee->designation_id = $request->designation_id;
                $employee->save();
            }

            // Return success response with the employee
            return $this->response(true, 'Employee Created Successfully', $employee, 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors and return the specific validation error messages
            return $this->response(false, 'Validation Error', $e->errors(), 422);

        } catch (\Exception $e) {
            // Handle all other exceptions
            return $this->response(false, 'Something went wrong', $e->getMessage(), 500);
        }
    }

    // Edit Employee
    public function editEmployee(Request $request, $id)
    {
        try {
            // Find the employee and user associated with the ID
            $employee = Employee::findOrFail($id);
            $user = User::findOrFail($employee->user_id);

            // Validate the incoming request
            $request->validate([
                'first_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'email' => 'nullable|string|email|max:255|unique:users,email,' . $user->id,
                'phone_num' => 'nullable|string|max:15',
                'gender' => 'nullable|string|max:10',
                'dept_id' => 'nullable|exists:departments,id',
                'designation_id' => 'nullable|exists:designations,id',
                'supervisor_id' => 'nullable|exists:users,id',
                'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            ]);

            // Update user details
            if ($request->has('first_name')) {
                $user->first_name = $request->first_name;
                $employee->first_name = $request->first_name;
            }
            if ($request->has('last_name')) {
                $user->last_name = $request->last_name;
                $employee->last_name = $request->last_name;
            }
            if ($request->has('email')) {
                $user->email = $request->email;
                $employee->email = $request->email;
            }

            // Handle profile picture update
            if ($request->hasFile('profile_picture')) {
                // Delete the old profile picture if exists
                if ($user->profile_photo_path) {
                    Storage::disk('public')->delete($user->profile_photo_path);
                }

                // Store the new profile picture
                $profilePicturePath = $request->file('profile_picture')->store('profile_pictures', 'public');
                $user->profile_photo_path = $profilePicturePath;
            }

            // Save user details
            $user->save();

            // Update employee details
            if ($request->has('phone_num')) {
                $employee->phone_num = $request->phone_num;
            }
            if ($request->has('gender')) {
                $employee->gender = $request->gender;
            }
            if ($request->has('dept_id')) {
                $employee->dept_id = $request->dept_id;
            }
            if ($request->has('designation_id')) {
                $employee->designation_id = $request->designation_id;
            }
            if ($request->has('supervisor_id')) {
                $employee->supervisor_id = $request->supervisor_id;
            }

            // Save employee details
            $employee->save();

            // Return success response with the updated employee
            return $this->response(true, 'Employee Updated Successfully', $employee, 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors and return the specific validation error messages
            return $this->response(false, 'Validation Error', $e->errors(), 422);

        } catch (\Exception $e) {
            // Handle all other exceptions
            return $this->response(false, 'Something went wrong', $e->getMessage(), 500);
        }
    }
    // Employee Details when edit
    public function getEmployeeDetails($id)
    {
        try {
            // Fetch the employee with associated user details
            $employee = Employee::with('user')->find($id);

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found',
                ], 404);
            }

            // Fetch all departments and designations for dropdowns
            $departments = Department::select('id', 'name')->get();
            $designations = Designation::select('id', 'name')->get();

            // Prepare the response data
            $responseData = [
                'employee' => [
                    'id' => $employee->id,
                    'first_name' => $employee->first_name,
                    'last_name' => $employee->last_name,
                    'email' => $employee->email,
                    'phone_num' => $employee->phone_num,
                    'gender' => $employee->gender,
                    'dept_id' => $employee->dept_id,
                    'designation_id' => $employee->designation_id,
                    'supervisor_id' => $employee->supervisor_id,
                    'profile_picture' => $employee->user->profile_photo_path
                        ? asset('storage/' . $employee->user->profile_photo_path)
                        : null,
                ],
                'departments' => $departments, // All departments
                'designations' => $designations, // All designations
            ];

            return response()->json([
                'success' => true,
                'message' => 'Employee details fetched successfully',
                'data' => $responseData,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }





    // Update Employee API

    public function updateEmployee(Request $request, $id)
    {
        try {
            // Validate the incoming request with all fields
            $request->validate([
                'first_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'email' => 'nullable|string|email|max:255|unique:users,email,' . $id,
                'phone_num' => 'nullable|string|max:15',
                'gender' => 'nullable|string|max:10',
                'dept_id' => 'nullable|exists:departments,id',
                'designation_id' => 'nullable|exists:designations,id',
                'office_shift' => 'nullable|string|max:255',
                'basic_salary' => 'nullable|string|max:255',
                'hourly_rate' => 'nullable|string|max:255',
                'payslip_type' => 'nullable|string|max:255',
                'date_of_birth' => 'nullable|date',
                'marital_status' => 'nullable|string|max:255',
                'district' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'zip_code' => 'nullable|string|max:255',
                'religion' => 'nullable|string|max:255',
                'blood_group' => 'nullable|string|max:255',
                'nationality' => 'nullable|string|max:255',
                'present_address' => 'nullable|string',
                'permanent_address' => 'nullable|string',
                'bio' => 'nullable|string',
                'experience' => 'nullable|integer',
                'facebook' => 'nullable|string|max:255',
                'linkedin' => 'nullable|string|max:255',
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Fetch the user and employee records using user_id
            $user = User::findOrFail($id); // Find user by ID
//            echo '<pre>';
//            print_r($id);
            $employee = Employee::where('user_id', $id)->firstOrFail(); // Find employee by user_id
//            echo '<pre>';
//            print_r($employee);

            // Update user fields if provided
            if ($request->has('first_name')) {
                $user->first_name = $request->first_name;
            }

            if ($request->has('last_name')) {
                $user->last_name = $request->last_name;
            }

            if ($request->has('email')) {
                $user->email = $request->email;
            }

            // Handle profile picture upload if exists
//            if ($request->hasFile('profile_picture')) {
//                // Delete the old profile picture if exists
//                if ($user->profile_photo_path && file_exists(storage_path('app/' . $user->profile_photo_path))) {
//                    unlink(storage_path('app/' . $user->profile_photo_path));
//                }
//
//                // Store the new profile picture and save the path
//                $profilePicturePath = $request->file('profile_picture')->store('profile_pictures','public');
//                $user->profile_photo_path = $profilePicturePath;
//            }

            if ($request->hasFile('profile_picture')) {
                // Delete the old profile picture if it exists
                if (!empty($user->profile_photo_path) && Storage::disk('public')->exists($user->profile_photo_path)) {
                    Storage::disk('public')->delete($user->profile_photo_path);
                }

                // Store the new profile picture in 'profile_pictures' folder and save the path
                $profilePicturePath = $request->file('profile_picture')->store('profile_pictures', 'public');
                $user->profile_photo_path = $profilePicturePath;
                $user->save(); // Ensure the new path is saved to the database
            }


            // Save the updated user
            $user->save();

            // Update employee fields if provided
            if ($request->has('first_name')) {
                $employee->first_name = $request->first_name;
            }

            if ($request->has('last_name')) {
                $employee->last_name = $request->last_name;
            }

            if ($request->has('email')) {
                $employee->email = $request->email;
            }

            if ($request->has('phone_num')) {
                $employee->phone_num = $request->phone_num;
            }

            if ($request->has('gender')) {
                $employee->gender = $request->gender;
            }

            if ($request->has('dept_id')) {
                $employee->dept_id = $request->dept_id;
            }

            if ($request->has('designation_id')) {
                $employee->designation_id = $request->designation_id;
            }

            if ($request->has('office_shift')) {
                $employee->office_shift = $request->office_shift;
            }

            if ($request->has('basic_salary')) {
                $employee->basic_salary = $request->basic_salary;
            }

            if ($request->has('hourly_rate')) {
                $employee->hourly_rate = $request->hourly_rate;
            }

            if ($request->has('payslip_type')) {
                $employee->payslip_type = $request->payslip_type;
            }

            if ($request->has('date_of_birth')) {
                $employee->date_of_birth = $request->date_of_birth;
            }

            if ($request->has('marital_status')) {
                $employee->marital_status = $request->marital_status;
            }

            if ($request->has('district')) {
                $employee->district = $request->district;
            }

            if ($request->has('city')) {
                $employee->city = $request->city;
            }

            if ($request->has('zip_code')) {
                $employee->zip_code = $request->zip_code;
            }

            if ($request->has('religion')) {
                $employee->religion = $request->religion;
            }

            if ($request->has('blood_group')) {
                $employee->blood_group = $request->blood_group;
            }

            if ($request->has('nationality')) {
                $employee->nationality = $request->nationality;
            }

            if ($request->has('present_address')) {
                $employee->present_address = $request->present_address;
            }

            if ($request->has('permanent_address')) {
                $employee->permanent_address = $request->permanent_address;
            }

            if ($request->has('bio')) {
                $employee->bio = $request->bio;
            }

            if ($request->has('experience')) {
                $employee->experience = $request->experience;
            }

            if ($request->has('facebook')) {
                $employee->facebook = $request->facebook;
            }

            if ($request->has('linkedin')) {
                $employee->linkedin = $request->linkedin;
            }

            // Save the updated employee data
            $employee->save();

            // Return success response
            return $this->response(true, 'Employee updated successfully', $employee, 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors and return the specific validation error messages
            return $this->response(false, 'Validation Error', $e->errors(), 422);

        } catch (\Exception $e) {
            // Handle all other exceptions
            return $this->response(false, 'Something went wrong', $e->getMessage(), 500);
        }
    }


    // Employee List API

    public function employeeList()
    {
        try {
            // Fetch employees with relationships and order by department creation date
            $employees = Employee::with([
                'user' => function ($query) {
                    $query->select('id', 'profile_photo_path', 'role_id', 'active_status');
                },
                'user.role' => function ($query) {
                    $query->select('id', 'name');
                },
                'department' => function ($query) {
                    $query->select('id', 'name', 'created_at');
                },
                'designation' => function ($query) {
                    $query->select('id', 'name');
                },
            ])
                ->whereNotNull('emp_id') // Ensure only employees with non-null emp_id are fetched
                ->get()
                ->sortBy(function ($employee) {
                    return $employee->department->created_at ?? now(); // Sort by department creation date
                });

            // Transform grouped employees data to the desired structure
            $formattedResponse = $employees->groupBy(function ($employee) {
                return $employee->department->name ?? 'Unassigned'; // Group by department name
            })->map(function ($group, $departmentName) {
                // Map grouped employees into individual records with department name
                return $group->map(function ($employee) use ($departmentName) {
                    return [
                        'id' => $employee->id,
                        'user_id' => $employee->user_id,
                        'profile_photo_path' => $employee->user->profile_photo_path ? url('storage/' . $employee->user->profile_photo_path) : null,
                        'role_name' => $employee->user->role->name ?? null,
                        'active_status' => $employee->user->active_status === 1 ? 'Active' : 'Inactive',
                        'supervisor_id' => $employee->supervisor_id,
                        'first_name' => $employee->first_name,
                        'last_name' => $employee->last_name,
                        'emp_id' => $employee->emp_id,
                        'email' => $employee->email,
                        'department_name' => $departmentName,
                        'designation_name' => $employee->designation->name ?? null,
                        'created_at' => $employee->created_at,
                        'updated_at' => $employee->updated_at,
                    ];
                });
            })->collapse(); // Flatten the grouped collection into a single list

            return $this->response(
                true,
                'Employee fetched successfully',
                $formattedResponse,
                200
            );
        } catch (\Exception $e) {
            // Handle any exceptions
            return $this->response(
                false,
                'Something went wrong',
                $e->getMessage(),
                500
            );
        }
    }







    // Employee Count
    public function countEmployeesWithEmpId()
    {
        try {
            // Count employees where emp_id is not null
            //$employeeCount = Employee::whereNotNull('emp_id')->count();
            $employeeCount = Employee::all()->count();

            // Return the count in the response
            return $this->response(
                true,
                'Employee count fetched successfully',
                ['count' => $employeeCount],
                200
            );

        } catch (\Exception $e) {
            // Handle any exceptions
            return $this->response(
                false,
                'Something went wrong',
                $e->getMessage(),
                500
            );
        }
    }

    // Delete Employee
    public function deleteEmployee($id)
    {
        DB::beginTransaction();  // Start a database transaction

        try {
            // Find the employee by ID
            $employee = Employee::find($id);

            if (!$employee) {
                return $this->response(
                    false,
                    'Employee not found',
                    null,
                    404
                );
            }

            // Find the user associated with the employee (assuming 'user_id' is the foreign key)
            $user = User::find($employee->user_id);

            if ($user) {
                // Delete the associated user record
                $user->delete();
            }

            // Delete the employee record
            $employee->delete();

            // Commit the transaction
            DB::commit();

            // Return a success response
            return $this->response(
                true,
                'Employee and corresponding user deleted successfully',
                null,
                200
            );
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollback();

            // Handle any exceptions
            return $this->response(
                false,
                'Something went wrong',
                $e->getMessage(),
                500
            );
        }
    }




















}
