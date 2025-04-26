<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LateType;
use Illuminate\Validation\ValidationException;

class LateTypeController extends Controller
{
    // Get list of all late types
    public function lateTypesList()
    {
        try {
            $lateTypes = LateType::all();

            return $this->response(
                true,
                'Late types retrieved successfully',
                $lateTypes,
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

    // Create a new late type
    public function createLateType(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|unique:late_types',
                'description' => 'nullable|string',
            ]);

            $lateType = LateType::create([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            return $this->response(
                true,
                'Late type created successfully',
                $lateType,
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

    // Update an existing late type
    public function updateLateType(Request $request, $id)
    {
        try {
            $lateType = LateType::findOrFail($id);

            $request->validate([
                'name' => 'required|unique:late_types,name,' . $id,
                'description' => 'nullable|string',
            ]);

            $lateType->update([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            return $this->response(
                true,
                'Late type updated successfully',
                $lateType,
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

    // Get details of a specific late type
    public function getLateTypeDetails($id)
    {
        try {
            $lateType = LateType::find($id);

            if (!$lateType) {
                return $this->response(
                    false,
                    'Late type not found',
                    null,
                    404
                );
            }

            return $this->response(
                true,
                'Late type retrieved successfully',
                $lateType,
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

    // Delete a late type
    public function deleteLateType($id)
    {
        try {
            $lateType = LateType::find($id);

            if (!$lateType) {
                return $this->response(
                    false,
                    'Late type not found',
                    null,
                    404
                );
            }

            $lateType->delete();

            return $this->response(
                true,
                'Late type deleted successfully',
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
