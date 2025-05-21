<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\PayrollAdjustment;
use App\Models\SalaryStructure;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class PayrollController extends Controller
{
//    public function payrolls()
//    {
//        try {
//            $payrolls = Payroll::with('employee', 'salaryStructure')->get();
//            return response()->json($payrolls, 200);
//        } catch (Exception $e) {
//            return response()->json([
//                'error' => 'Failed to fetch payroll records.',
//                'message' => $e->getMessage()
//            ], 500);
//        }
//    }


    public function payrolls()
    {
        try {
            $payrolls = Payroll::with([
                'employee:id,emp_id,phone_num,user_id',
                'employee.user:id,first_name,last_name,email,profile_photo_path',
                'salaryStructure:id,employee_id,basic_salary,allowance,monthly_deduction,tax_percentage',
                'salaryStructure'

            ])->get();

            $flatData = $payrolls->map(function ($item) {
                $cashAdvance = $item->payrollAdjustments->where('type', 'cash_advance')->sum('amount');
                return [

                    'id' => $item->id,
                    'employee_id' => $item->employee_id,
                    'emp_id' => $item->employee->emp_id ?? null,
                    'first_name' => $item->employee->user->first_name ?? null,
                    'last_name' => $item->employee->user->last_name ?? null,
                    'email' => $item->employee->user->email ?? null,
                    'phone_num' => $item->employee->phone_num ?? null,
                    'profile_photo_path' => $item->employee->user?->profile_photo_url, // accessor in User model

                    // Salary structure details
                    'basic_salary' => $item->salaryStructure->basic_salary ?? null,
                    'allowance' => $item->salaryStructure->allowance ?? null,
                    'monthly_deduction' => $item->salaryStructure->monthly_deduction ?? null,
                    'tax_percentage' => $item->salaryStructure->tax_percentage ?? null,

                    // Payroll details
                    'month' => $item->month,
                    'days_worked' => $item->days_worked,
                    'total_earnings' => $item->total_earnings,
                    'total_deductions' => $item->total_deductions,
                    'cash_advance' => $cashAdvance,
                    'net_pay' => $item->net_pay,
                    'status' => $item->status,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $flatData
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch payroll records.',
                'error' => $e->getMessage()
            ], 500);
        }
    }



//    public function createPayroll(Request $request)
//    {
//        try {
//            $request->validate([
//                'employee_id' => 'required|exists:employees,id',
//                'month' => 'required',
//                'days_worked' => 'required|integer|min:0|max:31'
//            ]);
//
//            $structure = SalaryStructure::where('employee_id', $request->employee_id)
//                ->orderByDesc('effective_date')
//                ->first();
//
//            if (!$structure) {
//                return response()->json(['error' => 'No salary structure found for this employee.'], 404);
//            }
//
//            $earnings = $structure->basic_salary + $structure->allowance;
//            $deductions = $structure->deduction + (($structure->tax_percentage / 100) * $earnings);
//            $netPay = $earnings - $deductions;
//
//            $payroll = Payroll::create([
//                'employee_id' => $request->employee_id,
//                'salary_structure_id' => $structure->id,
//                'month' => $request->month,
//                'days_worked' => $request->days_worked,
//                'total_earnings' => $earnings,
//                'total_deductions' => $deductions,
//                'net_pay' => $netPay,
//                'status' => 'pending'
//            ]);
//
//            return response()->json([
//                'status' => 'success',
//                'message' => 'Payroll generated successfully.',
//                'data' => $payroll
//            ], 201);
//        } catch (Exception $e) {
//            return response()->json([
//                'error' => 'Failed to generate payroll.',
//                'message' => $e->getMessage()
//            ], 500);
//        }
//    }
    public function createPayroll(Request $request)
    {
        try {
            $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'month' => 'required|date_format:Y-m',
                'days_worked' => 'nullable|integer|min:0|max:31'
            ]);

            // Get latest salary structure for the employee
            $structure = SalaryStructure::where('employee_id', $request->employee_id)
                ->orderByDesc('effective_date')
                ->first();

            if (!$structure) {
                return response()->json(['error' => 'No salary structure found for this employee.'], 404);
            }

            // Base earnings
            $baseEarnings = $structure->basic_salary + $structure->allowance;

            // Base deductions
            $baseDeductions = $structure->monthly_deduction +
                (($structure->tax_percentage / 100) * $baseEarnings);

            // Get all payroll adjustments for this employee and month
            $adjustments = PayrollAdjustment::where('employee_id', $request->employee_id)
                ->where('month', $request->month)
                ->get();

            // Sum adjustments
            $bonus = $adjustments->where('type', 'bonus')->sum('amount');
            $incentive = $adjustments->where('type', 'incentive')->sum('amount');
            $deduction = $adjustments->where('type', 'deduction')->sum('amount');
            $cashAdvance = $adjustments->where('type', 'cash_advance')->sum('amount');

            // Final earnings and deductions
            $totalEarnings = $baseEarnings + $bonus + $incentive;
            $totalDeductions = $baseDeductions + $deduction;

            // Net pay
            $netPay = $totalEarnings - $totalDeductions - $cashAdvance;

            // Create the payroll record
            $payroll = Payroll::create([
                'employee_id' => $request->employee_id,
                'salary_structure_id' => $structure->id,
                'month' => $request->month,
                'days_worked' => $request->days_worked ?? 0,
                'total_earnings' => $totalEarnings,
                'total_deductions' => $totalDeductions,
                'cash_advance' => $cashAdvance, // if you have this column
                'net_pay' => $netPay,
                'status' => 'pending'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Payroll generated successfully.',
                'data' => $payroll
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to generate payroll.',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function updatePayroll($id)
    {
        try {
            $payroll = Payroll::findOrFail($id);

            $payroll->update([
                'status' => 'paid',
                'payment_date' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Payroll marked as paid.'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Payroll not found.'], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to update payroll status.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function deletePayroll($id)
    {
        $payroll = Payroll::findOrFail($id);
        $payroll->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Payroll record deleted successfully.'
        ]);
    }

}
