<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Support\Facades\Auth;
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


//    public function payrolls()
//    {
//        try {
//            $payrolls = Payroll::with([
//                'employee:id,emp_id,phone_num,user_id',
//                'employee.user:id,first_name,last_name,email,profile_photo_path',
//                'salaryStructure:id,employee_id,basic_salary,allowance,monthly_deduction,tax_percentage',
//                'salaryStructure'
//
//            ])->get();
//
//            $flatData = $payrolls->map(function ($item) {
//                $cashAdvance = $item->payrollAdjustments->where('type', 'cash_advance')->sum('amount');
//                return [
//
//                    'id' => $item->id,
//                    'employee_id' => $item->employee_id,
//                    'emp_id' => $item->employee->emp_id ?? null,
//                    'first_name' => $item->employee->user->first_name ?? null,
//                    'last_name' => $item->employee->user->last_name ?? null,
//                    'email' => $item->employee->user->email ?? null,
//                    'phone_num' => $item->employee->phone_num ?? null,
//                    'profile_photo_path' => $item->employee->user?->profile_photo_url, // accessor in User model
//
//                    // Salary structure details
//                    'basic_salary' => $item->salaryStructure->basic_salary ?? null,
//                    'allowance' => $item->salaryStructure->allowance ?? null,
//                    'monthly_deduction' => $item->salaryStructure->monthly_deduction ?? null,
//                    'tax_percentage' => $item->salaryStructure->tax_percentage ?? null,
//
//                    // Payroll details
//                    'month' => $item->month,
//                    'days_worked' => $item->days_worked,
//                    'total_earnings' => $item->total_earnings,
//                    'total_deductions' => $item->total_deductions,
//                    'cash_advance' => $cashAdvance,
//                    'net_pay' => $item->net_pay,
//                    'status' => $item->status,
//                    'payment_by' => $item->payment_by,
//                    'payment_date' => $item->payment_date,
//                    'created_at' => $item->created_at,
//                    'updated_at' => $item->updated_at,
//                ];
//            });
//
//            return response()->json([
//                'status' => 'success',
//                'data' => $flatData
//            ], 200);
//        } catch (Exception $e) {
//            return response()->json([
//                'status' => 'error',
//                'message' => 'Failed to fetch payroll records.',
//                'error' => $e->getMessage()
//            ], 500);
//        }
//    }
    public function payrolls(Request $request)
    {
        try {
            // Check if a month filter is provided via query parameter
            $month = $request->query('month'); // Example: '2025-06'

            $query = Payroll::with([
                'employee:id,emp_id,phone_num,user_id,designation_id,dept_id,bank_account_number',
                'employee.user:id,first_name,last_name,email,profile_photo_path',
                'employee.designation:id,name',
                'employee.department:id,name',
                'salaryStructure:id,employee_id,basic_salary,allowance,monthly_deduction,tax_percentage',
            ]);

            // If a month is provided, filter the records by month
            if ($month) {
                $query->where('month', $month); // Assumes 'month' column is in 'YYYY-MM' format
            }

            $payrolls = $query->get();

            $flatData = $payrolls->map(function ($item) {
                // Sum all cash advance amounts from payroll adjustments
                $cashAdvance = $item->payrollAdjustments->where('type', 'cash_advance')->sum('amount');

                return [
                    'id' => $item->id,
                    'employee_id' => $item->employee_id,
                    'emp_id' => $item->employee->emp_id ?? null,
                    'bank_account_number' => $item->employee->bank_account_number ?? null,
                    'designation_name' => $item->employee->designation->name ?? null,
                    'department_name' => $item->employee->department->name ?? null,
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
                    'payment_by' => $item->payment_by,
                    'payment_date' => $item->payment_date,
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
//    public function createPayroll(Request $request)
//    {
//        try {
//            $request->validate([
//                'employee_id' => 'required|exists:employees,id',
//                'month' => 'required|date_format:Y-m',
//                'days_worked' => 'nullable|integer|min:0|max:31'
//            ]);
//
//            // Get latest salary structure for the employee
//            $structure = SalaryStructure::where('employee_id', $request->employee_id)
//                ->orderByDesc('effective_date')
//                ->first();
//
//            if (!$structure) {
//                return response()->json(['error' => 'No salary structure found for this employee.'], 404);
//            }
//
//            // Base earnings
//            $baseEarnings = $structure->basic_salary + $structure->allowance;
//
//            // Base deductions
//            $baseDeductions = $structure->monthly_deduction +
//                (($structure->tax_percentage / 100) * $baseEarnings);
//
//            // Get all payroll adjustments for this employee and month
//            $adjustments = PayrollAdjustment::where('employee_id', $request->employee_id)
//                ->where('month', $request->month)
//                ->get();
//
//            // Sum adjustments
//            $bonus = $adjustments->where('type', 'bonus')->sum('amount');
//            $incentive = $adjustments->where('type', 'incentive')->sum('amount');
//            $deduction = $adjustments->where('type', 'deduction')->sum('amount');
//            $cashAdvance = $adjustments->where('type', 'cash_advance')->sum('amount');
//
//            // Final earnings and deductions
//            $totalEarnings = $baseEarnings + $bonus + $incentive;
//            $totalDeductions = $baseDeductions + $deduction;
//
//            // Net pay
//            $netPay = $totalEarnings - $totalDeductions - $cashAdvance;
//
//            // Create the payroll record
//            $payroll = Payroll::create([
//                'employee_id' => $request->employee_id,
//                'salary_structure_id' => $structure->id,
//                'month' => $request->month,
//                'days_worked' => $request->days_worked ?? 0,
//                'total_earnings' => $totalEarnings,
//                'total_deductions' => $totalDeductions,
//                'cash_advance' => $cashAdvance, // if you have this column
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
                'month' => 'required|date_format:Y-m',
            ]);

//            $employees = Employee::all();
            $employees = Employee::withActiveUser()->get();


            $createdPayrolls = [];
            $skippedEmployees = [];

            foreach ($employees as $employee) {
                //  Skip if payroll for this employee & month already exists
                $existing = Payroll::where('employee_id', $employee->id)
                    ->where('month', $request->month)
                    ->first();

                if ($existing) {
                    $skippedEmployees[] = $employee->id;
                    continue;
                }

                //  Get latest salary structure
                $structure = SalaryStructure::where('employee_id', $employee->id)
                    ->orderByDesc('effective_date')
                    ->first();

                if (!$structure) {
                    $skippedEmployees[] = $employee->id;
                    continue;
                }

                $baseEarnings = $structure->basic_salary + $structure->allowance;
                $baseDeductions = $structure->monthly_deduction +
                    (($structure->tax_percentage / 100) * $baseEarnings);

                $adjustments = PayrollAdjustment::where('employee_id', $employee->id)
                    ->where('month', $request->month)
                    ->get();

                $bonus = $adjustments->where('type', 'bonus')->sum('amount');
                $incentive = $adjustments->where('type', 'incentive')->sum('amount');
                $deduction = $adjustments->where('type', 'deduction')->sum('amount');
                $cashAdvance = $adjustments->where('type', 'cash_advance')->sum('amount');

                $totalEarnings = $baseEarnings + $bonus + $incentive;
                $totalDeductions = $baseDeductions + $deduction;
                $netPay = $totalEarnings - $totalDeductions - $cashAdvance;

                $payroll = Payroll::create([
                    'employee_id' => $employee->id,
                    'salary_structure_id' => $structure->id,
                    'month' => $request->month,
                    'days_worked' => 0,
                    'total_earnings' => $totalEarnings,
                    'total_deductions' => $totalDeductions,
                    'cash_advance' => $cashAdvance,
                    'net_pay' => $netPay,
                    'status' => 'pending'
                ]);

                $createdPayrolls[] = $payroll;
            }

            return response()->json([
                'status' => 'success',
                'message' => count($createdPayrolls) . ' payroll(s) generated.',
                'skipped' => $skippedEmployees,
                'data' => $createdPayrolls
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to generate payrolls.',
                'message' => $e->getMessage()
            ], 500);
        }
    }



    public function updatePayroll($id)
    {
        try {
            // Fetch the logged-in user
            $user = Auth::user();

            // Check if user is Accountant or HR

            if (!$user || !in_array($user->role_id, [2, 6])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized! Only Accountant and HR can pay salary.',
                ], 403);
            }


            $payroll = Payroll::findOrFail($id);


            $payroll->payment_by = $user->first_name . ' ' . $user->last_name;


            $payroll->update([
                'status' => 'paid',
                'payment_by' => $payroll->payment_by,
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
