<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Department;
use App\Models\Designation;
use Carbon\Carbon;

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
                'role_id' => 'nullable|exists:roles,id',
                'emp_id' => 'nullable|string|max:50',
                'basic_salary' => 'nullable|string|max:50',
                'contract_date' => 'nullable|string|max:50',
                'contract_end' => 'nullable|string|max:50',
                'leave_categories' => 'nullable|string|max:255',
                'role_description' => 'nullable|string|max:255',
                'office_shift' => 'nullable|string|max:50',
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
            $user->role_id = $request->role_id;

            // Handle profile picture upload if exists
            if ($request->hasFile('profile_picture')) {
                // Store the image and get its path
                $profilePicturePath = $request->file('profile_picture')->store('profile_pictures', 'public');
                // Save the path in the database
                $user->profile_photo_path = $profilePicturePath;
//
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
                $employee->basic_salary = $request->basic_salary;
                $employee->office_shift = $request->office_shift;
                $employee->leave_categories = $request->leave_categories;
                $employee->role_description = $request->role_description;
                $employee->contract_date = $request->contract_date;
                $employee->contract_end = $request->contract_end;
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
                'phone_num' => 'nullable|string|min:11|max:15',
                'gender' => 'nullable|string|max:10',
                'office_shift' => 'nullable|string|max:50',
                'contract_date' => 'nullable|date',
                'contract_end' => 'nullable|date',
                'leave_categories' => 'nullable|string|max:255',
                'role_description' => 'nullable|string|max:255',
                'basic_salary' => 'nullable|string|max:255',
                'dept_id' => 'nullable|exists:departments,id',
                'designation_id' => 'nullable|exists:designations,id',
                'supervisor_id' => 'nullable|exists:users,id',
                'role_id' => 'nullable|exists:roles,id',
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
            if ($request->has('role_id')) {
                $user->role_id = $request->role_id;
            }
            if ($request->has('active_status')) {
                $user->active_status = $request->active_status;
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
            if ($request->has('basic_salary')) {
                $employee->basic_salary = $request->basic_salary;
            }
            if ($request->has('office_shift')) {
                $employee->office_shift = $request->office_shift;
            }
            if ($request->has('contract_date')) {
                $employee->contract_date = $request->contract_date;
            }
            if ($request->has('contract_end')) {
                $employee->contract_end = $request->contract_end;
            }
            if ($request->has('leave_categories')) {
                $employee->leave_categories = $request->leave_categories;
            }
            if ($request->has('role_description')) {
                $employee->role_description = $request->role_description;
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
            $roles = Role::select('id','name')->get();

            $employee->contract_date = Carbon::parse($employee->contract_date);
            $employee->contract_end = Carbon::parse($employee->contract_end);

            // Prepare the response data
            $responseData = [
                'employee' => [
                    'id' => $employee->id,
                    'first_name' => $employee->first_name,
                    'last_name' => $employee->last_name,
                    'email' => $employee->email,
                    'phone_num' => $employee->phone_num,
                    'gender' => $employee->gender,
                    'role_id' => $employee->user->role_id,
                    'dept_id' => $employee->dept_id,
                    'active_status' => $employee->user->active_status,
                    'office_shift' => $employee->office_shift,
                    'designation_id' => $employee->designation_id,
                    'supervisor_id' => $employee->supervisor_id,
                    'contract_date' => $employee->contract_date ? $employee->contract_date->format('Y-m-d') : null,
                    'contract_end' => $employee->contract_end ? $employee->contract_end->format('Y-m-d') : null,
                    'leave_categories' => $employee->leave_categories,
                    'role_description' => $employee->role_description,
                    'basic_salary' => $employee->basic_salary,
                    'profile_picture' => $employee->user->profile_photo_path
                        ? asset('storage/' . $employee->user->profile_photo_path)
                        : null,
                ],
                'departments' => $departments, // All departments
                'designations' => $designations, // All designations
                'roles' => $roles,
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
                'phone_num' => 'nullable|string|min:11|max:15',
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
                'github' => 'nullable|string|max:255',
                'bank_name' => 'nullable|string|max:255',
                'bank_account_number' => 'nullable|string|max:50|regex:/^[0-9]{6,20}$/',
                'branch' => 'nullable|string|max:255',
                'emg_contact_name' => 'nullable|string|max:255',
                'emg_relationship' => 'nullable|string|max:50',
                'emg_phone_number' => 'nullable|string|max:20|',
                'emg_address' => 'nullable|string|max:500',
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
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
            if ($request->has('github')) {
                $employee->github = $request->github;
            }
            if ($request->has('bank_name')) {
                $employee->bank_name = $request->bank_name;
            }
            if ($request->has('bank_account_number')) {
                $employee->bank_account_number = $request->bank_account_number;
            }
            if ($request->has('branch')) {
                $employee->branch = $request->branch;
            }
            if ($request->has('emg_contact_name')) {
                $employee->emg_contact_name = $request->emg_contact_name;
            }
            if ($request->has('emg_relationship')) {
                $employee->emg_relationship = $request->emg_relationship;
            }
            if ($request->has('emg_phone_number')) {
                $employee->emg_phone_number = $request->emg_phone_number;
            }
            if ($request->has('emg_address')) {
                $employee->emg_address = $request->emg_address;
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
                        'phone_num' => $employee->phone_num,
                        'blood_group' => $employee->blood_group,
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


    // Employee Profile Details API

    // Top Left Some info , Specific Employee Details
    public function specificEmployeeDetails($id)
    {
        try {
            // Fetch employee with relationships based on the provided ID
            $employee = Employee::with([
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
            ])->find($id);

            // Check if the employee exists
            if (!$employee) {
                return $this->response(
                    false,
                    'Employee not found',
                    null,
                    404
                );
            }

            $employee->contract_date = Carbon::parse($employee->contract_date);
            $employee->contract_end = Carbon::parse($employee->contract_end);

            // Fetch all departments and designations
            $departments = Department::select('id', 'name')->get();
            $designations = Designation::select('id', 'name')->get();

            // Format the employee response data
            $formattedEmployee = [
                'id' => $employee->id,
                'user_id' => $employee->user_id,
                'profile_photo' => $employee->user->profile_photo_path ? url('storage/' . $employee->user->profile_photo_path) : null,
                'role_name' => $employee->user->role->name ?? null,
                'active_status' => $employee->user->active_status === 1 ? 'Active' : 'Inactive',
                'supervisor_id' => $employee->supervisor_id,
                'first_name' => $employee->first_name,
                'last_name' => $employee->last_name,
                'emp_id' => $employee->emp_id,
                'email' => $employee->email,
                'department_name' => $employee->department->name ?? 'Unassigned',
                'designation_name' => $employee->designation->name ?? 'Not Assigned',
                'contract_date' => $employee->contract_date ? $employee->contract_date->format('Y-m-d') : null,
                'contract_end' => $employee->contract_end ? $employee->contract_end->format('Y-m-d') : null,
                'basic_salary' => $employee->basic_salary,
                'hourly_rate' => $employee->hourly_rate,
                'payslip_type' => $employee->payslip_type,
                'office_shift' => $employee->office_shift,
                'leave_categories' => $employee->leave_categories,
                'role_description' => $employee->role_description,
                'phone_num' => $employee->phone_num,
                'gender' => $employee->gender,
                'date_of_birth' => $employee->date_of_birth,
                'marital_status' => $employee->marital_status,
                'district' => $employee->district,
                'city' => $employee->city,
                'zip_code' => $employee->zip_code,
                'religion' => $employee->religion,
                'blood_group' => $employee->blood_group,
                'nationality' => $employee->nationality,
                'present_address' => $employee->present_address,
                'permanent_address' => $employee->permanent_address,
                'bio' => $employee->bio,
                'experience' => $employee->experience,
                'facebook' => $employee->facebook,
                'linkedin' => $employee->linkedin,
                'citizenship' => $employee->citizenship,
                'github' => $employee->github,
                'bank_name' => $employee->bank_name,
                'bank_account_number' => $employee->bank_account_number,
                'branch' => $employee->branch,
                'emg_contact_name' => $employee->emg_contact_name,
                'emg_relationship' => $employee->emg_relationship,
                'emg_phone_number' => $employee->emg_phone_number,
                'emg_address' => $employee->emg_address,
                'created_at' => $employee->created_at,
                'updated_at' => $employee->updated_at,
            ];

            // Combine employee data with departments and designations
            $response = [
                'employee' => $formattedEmployee,
                'departments' => $departments,
                'designations' => $designations,
            ];

            return $this->response(
                true,
                'Employee details fetched successfully',
                $response,
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





    //password change by admin,hr and employee

//        public function resetPassword(Request $request)
//    {
//        $user = Auth::user();
//
//        if ($user->role === 'employee') {
//            // Validate current password and new passwords
//            $request->validate([
//                'current_password' => 'required',
//                'new_password' => 'required|confirmed|min:8',
//            ]);
//
//            if (!Hash::check($request->current_password, $user->password)) {
//                return response()->json(['error' => 'Current password is incorrect'], 400);
//            }
//
//            $user->password = Hash::make($request->new_password);
//        } elseif (in_array($user->role, ['admin', 'hr'])) {
//            // Admin/HR can directly reset the password
//            $request->validate(['employee_id' => 'required|exists:users,id']);
//
//            $employee = User::findOrFail($request->employee_id);
//            $employee->password = Hash::make('DefaultPassword123'); // Or generate random
//        }
//
//        $user->save();
//
//        return response()->json(['message' => 'Password successfully updated']);
//    }



















}
