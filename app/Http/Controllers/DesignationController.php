<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Designation;
use Illuminate\Validation\ValidationException;

class DesignationController extends Controller
{
    public function createDesignation(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'dept_id' => 'required|exists:departments,id',
                'name' => 'required|string|max:255|unique:designations,name',
                'description' => 'nullable|string|max:500',
            ]);

            // Create the new designation
            $designation = new Designation();
            $designation->dept_id = $request->dept_id;
            $designation->name = $request->name;
            $designation->description = $request->description;
            $designation->save();

            // Return success response
            return $this->response(
                true,
                'Designation created successfully',
                $designation,
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

    // All Designations
    public function designationList()
    {
        try {
            // Retrieve all designations from the database
            $designations = Designation::all();

            // Return the list of designations
            return $this->response(
                true,
                'Designations retrieved successfully',
                $designations,
                200
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


    // Delete Designation
    public function deleteDesignation($id)
    {
        try {
            // Find the designation by ID
            $designation = Designation::find($id);

            // Check if the designation exists
            if (!$designation) {
                return $this->response(
                    false,
                    'Designation not found',
                    null,
                    404
                );
            }

            // Delete the designation
            $designation->delete();

            // Return success response
            return $this->response(
                true,
                'Designation deleted successfully',
                null,
                200
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











}
