<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use Illuminate\Validation\ValidationException;

class DepartmentController extends Controller
{
    public function createDepartment(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'name' => 'required|string|max:255|unique:departments,name',
                'head_name' => 'required|string|max:255',
            ]);

            // Create a new department
            $department = new Department();
            $department->name = $request->name;
            $department->head_name = $request->head_name;
            $department->save();

            // Return success response
            return $this->response(
                true,
                'Department created successfully',
                $department,
                201
            );

        } catch (ValidationException $e) {
            // Handle validation errors
            return $this->response(
                false,
                'Validation Error',
                $e->errors(),
                422
            );

        } catch (\Exception $e) {
            // Handle any other exceptions
            return $this->response(
                false,
                'Something went wrong',
                $e->getMessage(),
                500
            );
        }
    }


    // All Departments
    public function getDepartments(Request $request)
    {
        try {
            // Get all departments
            $departments = Department::all();

            // Return success response with the department data
            return $this->response(
                true,
                'Departments fetched successfully',
                $departments,
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


    // Update Department
    public function updateDepartment(Request $request, $id)
    {
        try {
            // Validate the request
            $request->validate([
                'name' => 'required|string|max:255|unique:departments,name,' . $id, // Exclude current record from unique validation
                'head_name' => 'required|string|max:255',
            ]);

            // Find the department by ID
            $department = Department::findOrFail($id);

            // Update the department details
            $department->name = $request->name;
            $department->head_name = $request->head_name;
            $department->save();

            // Return success response
            return $this->response(
                true,
                'Department updated successfully',
                $department,
                200
            );

        } catch (ValidationException $e) {
            // Handle validation errors
            return $this->response(
                false,
                'Validation Error',
                $e->errors(),
                422
            );

        } catch (\Exception $e) {
            // Handle any other exceptions
            return $this->response(
                false,
                'Something went wrong',
                $e->getMessage(),
                500
            );
        }
    }

    // See Specific Department
    public function getDepartmentByID($id)
    {
        try {
            // Find the department by ID
            $department = Department::findOrFail($id);

            // Return success response with the department data
            return $this->response(
                true,
                'Department fetched successfully',
                $department,
                200
            );
        } catch (\Exception $e) {
            // Handle any exceptions (e.g., department not found)
            return $this->response(
                false,
                'Department not found',
                $e->getMessage(),
                404
            );
        }
    }



    // Delete Department API
    public function deleteDepartment($id)
    {
        try {
            // Find the department by ID
            $department = Department::findOrFail($id);

            // Delete the department
            $department->delete();

            // Return success response
            return $this->response(
                true,
                'Department deleted successfully',
                null,
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






}
