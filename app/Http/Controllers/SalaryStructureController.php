<?php

namespace App\Http\Controllers;

use App\Models\SalaryStructure;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class SalaryStructureController extends Controller
{
    public function salaryStructureList()
    {
        try {
            $structures = SalaryStructure::with([
                'employee:id,emp_id,phone_num,user_id',
                'employee.user:id,first_name,last_name,email,profile_photo_path'
            ])->get();

            $flatData = $structures->map(function ($item) {
                return [
                    'id' => $item->id,
                    'employee_id' => $item->employee_id,
                    'emp_id' => $item->employee->emp_id ?? null,
                    'first_name' => $item->employee->user->first_name ?? null,
                    'last_name' => $item->employee->user->last_name ?? null,
                    'email' => $item->employee->user->email ?? null,
                    'phone_num' => $item->employee->phone_num ?? null,
                    'profile_photo_path' => $item->employee->user?->profile_photo_url, // Accessor in User model
                    'basic_salary' => $item->basic_salary,
                    'allowance' => $item->allowance,
                    'monthly_deduction' => $item->monthly_deduction,
                    'tax_percentage' => $item->tax_percentage,
                    'effective_date' => $item->effective_date,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $flatData
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch salary structures.',
                'error' => $e->getMessage()
            ], 500);
        }
    }




    public function createSalaryStructure(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_id'     => 'required|exists:employees,id',
                'basic_salary'    => 'required|numeric',
                'allowance'       => 'nullable|numeric',
                'monthly_deduction' => 'nullable|numeric',
                'tax_percentage'  => 'nullable|numeric|min:0|max:100',
                'effective_date'  => 'required|date',
            ]);

            // Set default values for nullable fields if null
            $validated['monthly_deduction'] = $validated['monthly_deduction'] ?? 0;
            $validated['tax_percentage'] = $validated['tax_percentage'] ?? 0;

            $structure = SalaryStructure::create($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Salary structure created successfully.',
                'data' => [
                    'employee_id'    => $structure->employee_id,
                    'basic_salary'   => $structure->basic_salary,
                    'allowance'      => $structure->allowance,
                    'monthly_deduction'  => $structure->monthly_deduction,
                    'tax_percentage' => $structure->tax_percentage,
                    'effective_date' => $structure->effective_date,
                ]
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to create salary structure.',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function getSalaryStructureDetails($id)
    {
        try {
            $structure = SalaryStructure::findOrFail($id);

            // Return the response wrapped in your desired format
            return response()->json([
                'status' => 'success',
                'data' => $structure,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Salary structure not found.'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch salary structure.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function updateSalaryStructure(Request $request, $id)
    {
        try {
            $structure = SalaryStructure::findOrFail($id);

            $validated = $request->validate([
                'basic_salary'    => 'sometimes|required|numeric',
                'allowance'       => 'nullable|numeric',
                'monthly_deduction'       => 'nullable|numeric',
                'tax_percentage'  => 'nullable|numeric|min:0|max:100',
                'effective_date'  => 'sometimes|required|date',
            ]);

            $structure->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Salary structure updated successfully.',
                'data' => $structure
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Salary structure not found.'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to update salary structure.', 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteSalaryStructure($id)
    {
        try {
            $structure = SalaryStructure::findOrFail($id);
            $structure->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Salary structure deleted successfully.'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Salary structure not found.'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to delete salary structure.', 'message' => $e->getMessage()], 500);
        }
    }
}
