<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryStructure extends Model
{
    protected $fillable = [
        'employee_id', 'basic_salary', 'allowance', 'monthly_deduction', 'tax_percentage', 'effective_date'
    ];

//    public function employee()
//    {
//        return $this->belongsTo(Employee::class);
//    }
//    public function employee()
//    {
//        return $this->belongsTo(User::class, 'employee_id');
//    }
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


}

