<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveType;
use Illuminate\Validation\ValidationException;

class LeaveTypeController extends Controller
{
    // Get list of all leave types
    public function leaveTypesList()
    {
        try {
            // Retrieve all leave types
            $leaveTypes = LeaveType::all();

            return $this->response(
                true,
                'Leave types retrieved successfully',
                $leaveTypes,
                200
            );
        } catch (\Exception $e) {
            return $this->response(
                false,
                'Something went wrong',
                $e->getMessage(),
                500
            );
        }
    }

    // Create a new leave type
    public function createLeaveType(Request $request)
    {
        try {
            // Validate the incoming request
            $request->validate([
                'name' => 'required|unique:leave_types',
                'days_per_year' => 'required|integer|min:0',
                'requires_approval' => 'required|string|in:yes,no',
            ]);

            // Convert 'yes'/'no' to boolean
            $requiresApproval = $request->requires_approval === 'yes';

            // Create the leave type
            $leaveType = LeaveType::create([
                'name' => $request->name,
                'days_per_year' => $request->days_per_year,
                'requires_approval' => $requiresApproval,
            ]);

            return $this->response(
                true,
                'Leave type created successfully',
                $leaveType,
                201
            );
        } catch (ValidationException $e) {
            return $this->response(
                false,
                'Validation Error',
                $e->errors(),
                422
            );
        } catch (\Exception $e) {
            return $this->response(
                false,
                'Something went wrong',
                $e->getMessage(),
                500
            );
        }
    }

    // Update a leave type
    public function updateLeaveType(Request $request, $id)
    {
        try {
            // Find the leave type by ID
            $leaveType = LeaveType::findOrFail($id);

            // Validate the incoming request
            $request->validate([
                'name' => 'required|unique:leave_types,name,' . $id,
                'days_per_year' => 'required|integer|min:0',
                'requires_approval' => 'required|string|in:yes,no',
            ]);

            // Convert 'yes'/'no' to boolean
            $requiresApproval = $request->requires_approval === 'yes';

            // Update the leave type with the new values
            $leaveType->update([
                'name' => $request->name,
                'days_per_year' => $request->days_per_year,
                'requires_approval' => $requiresApproval,
            ]);

            return $this->response(
                true,
                'Leave type updated successfully',
                $leaveType,
                200
            );
        } catch (ValidationException $e) {
            return $this->response(
                false,
                'Validation Error',
                $e->errors(),
                422
            );
        } catch (\Exception $e) {
            return $this->response(
                false,
                'Something went wrong',
                $e->getMessage(),
                500
            );
        }
    }

    // Get details of a specific leave type
    public function getLeaveTypeDetails($id)
    {
        try {
            // Find the leave type by ID
            $leaveType = LeaveType::find($id);

            if (!$leaveType) {
                return $this->response(
                    false,
                    'Leave type not found',
                    null,
                    404
                );
            }

            return $this->response(
                true,
                'Leave type retrieved successfully',
                $leaveType,
                200
            );
        } catch (\Exception $e) {
            return $this->response(
                false,
                'Something went wrong',
                $e->getMessage(),
                500
            );
        }
    }

    // Delete a leave type
    public function deleteLeaveType($id)
    {
        try {
            // Find the leave type by ID
            $leaveType = LeaveType::find($id);

            if (!$leaveType) {
                return $this->response(
                    false,
                    'Leave type not found',
                    null,
                    404
                );
            }

            // Delete the leave type
            $leaveType->delete();

            return $this->response(
                true,
                'Leave type deleted successfully',
                null,
                200
            );
        } catch (\Exception $e) {
            return $this->response(
                false,
                'Something went wrong',
                $e->getMessage(),
                500
            );
        }
    }
}
