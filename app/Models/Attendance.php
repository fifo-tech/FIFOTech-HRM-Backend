<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id', 'date', 'status', 'clock_in', 'clock_in_reason',
        'clock_out', 'clock_out_reason', 'early_leaving', 'total_work_hour',
        'ip_address', 'device', 'location', 'late','date', 'daily_work_updates'
    ];

    // Many-to-one relationship (many attendances belong to one employee)
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }


}
