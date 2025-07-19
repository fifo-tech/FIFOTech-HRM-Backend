<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Notifications\Notifiable;

class Employee extends Model
{
    use Notifiable;

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
        'contract_date',
        'contract_end',
        'leave_categories',
        'role_description',
        'github',
        'citizenship',
    ];

    protected $dates = ['contract_date', 'contract_end', 'date_of_birth'];



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
        return $this->belongsTo(User::class, 'user_id','id');
    }

    // One-to-many relationship (one employee has many attendances)
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'employee_id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }
    // app/Models/Employee.php

//    public function scopeActive($query)
//    {
//        return $query->where('status', 'active');
//    }

//    here shows how to call only active employees
//    $employees = Employee::active()->get();

//    protected static function booted()
//    {
//        static::addGlobalScope('active', function (Builder $builder) {
//            $builder->where('status', 'active');
//        });
//    }

//    public function scopeActiveUser($query)
//    {
//        return $query->whereHas('user', function ($q) {
//            $q->where('status', 'active');
//        });
//    }



    public function scopeWithActiveUser($query)
    {
        return $query->whereHas('user', function ($q) {
            $q->where('active_status', 1);
        });
    }






}
