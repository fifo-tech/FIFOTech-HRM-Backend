<?php
namespace App\Http\Controllers;

use App\Models\PayrollAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PayrollAdjustmentController extends Controller
{
    public function payrollAdjustmentsList()
    {
        try {
            $adjustments = PayrollAdjustment::with([
                'employee:id,emp_id,phone_num,user_id',
                'employee.user:id,first_name,last_name,email,profile_photo_path'
            ])->get();

            $flatData = $adjustments->map(function ($item) {
                return [
                    'id' => $item->id,
                    'employee_id' => $item->employee_id,
                    'emp_id' => $item->employee->emp_id ?? null,
                    'first_name' => $item->employee->user->first_name ?? null,
                    'last_name' => $item->employee->user->last_name ?? null,
                    'email' => $item->employee->user->email ?? null,
                    'phone_num' => $item->employee->phone_num ?? null,
                    'profile_photo_path' => $item->employee->user?->profile_photo_url, // accessor
                    'type' => $item->type,
                    'amount' => $item->amount,
                    'description' => $item->description,
                    'month' => $item->month,
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
                'message' => 'Failed to fetch payroll adjustments.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function createPayrollAdjustment(Request $request)
    {
        try {
            // Normalize the type
            $request->merge([
                'type' => strtolower($request->type),
            ]);

            // Then validate
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'type' => 'required|in:bonus,incentive,cash_advance,deduction',
                'amount' => 'required|numeric|min:0',
                'month' => 'required|date_format:Y-m',
                'description' => 'nullable|string'
            ]);

            // Create the adjustment
            $adjustment = PayrollAdjustment::create($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Payroll adjustment created successfully.',
                'data' => $adjustment,
            ], 201);

        } catch (ValidationException $ve) {
            return response()->json([
                'status' => 'validation_error',
                'message' => 'Validation failed.',
                'errors' => $ve->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create payroll adjustment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function getPayrollAdjustmentDetails($id)
    {
        try {
            $adjustment = PayrollAdjustment::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $adjustment,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payroll adjustment not found.'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch payroll adjustment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updatePayrollAdjustment(Request $request, $id)
    {
        $adjustment = PayrollAdjustment::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'type' => 'sometimes|in:bonus,incentive,cash_advance,deduction',
            'amount' => 'sometimes|numeric|min:0',
            'month' => 'sometimes|date_format:Y-m',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $adjustment->update($request->all());
        return response()->json(['message' => 'Adjustment updated successfully', 'data' => $adjustment]);
    }

    public function deletePayrollAdjustment($id)
    {
        $adjustment = PayrollAdjustment::findOrFail($id);
        $adjustment->delete();

        return response()->json(['message' => 'Adjustment deleted successfully']);
    }
}

