<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'emp_id',
        'phone_num',
        'gender',
        'dept_id',
        'designation_id',
        'email',
        'office_shift',
        'basic_salary',
        'hourly_rate',
        'payslip_type',
        'date_of_birth',
        'marital_status',
        'district',
        'city',
        'zip_code',
        'religion',
        'blood_group',
        'nationality',
        'present_address',
        'permanent_address',
        'bio',
        'experience',
        'facebook',
        'linkedin',
    ];



    /**
     * Relationship with Department.
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'dept_id')->withDefault();
    }

    /**
     * Relationship with Designation.
     */
    public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation_id')->withDefault();
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // One-to-many relationship (one employee has many attendances)
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'employee_id');
    }


}
