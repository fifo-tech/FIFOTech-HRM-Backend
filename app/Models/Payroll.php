<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $fillable = [
        'employee_id', 'salary_structure_id', 'month', 'days_worked',
        'total_earnings', 'total_deductions', 'net_pay', 'status', 'payment_date','cash_advance'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function salaryStructure()
    {
        return $this->belongsTo(SalaryStructure::class);
    }
    public function payrollAdjustments()
    {
        return $this->hasMany(PayrollAdjustment::class, 'employee_id', 'employee_id');
    }
//    public function payrollAdjustments()
//    {
//        return $this->hasMany(PayrollAdjustment::class, 'employee_id', 'employee_id');
//            ->where('month', $this->month);
//    }


}

