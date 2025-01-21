<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    protected $fillable = [
        'department_id','name','description'
    ];
    // Define the relationship with the Department model
    public function department()
    {
        return $this->belongsTo(Department::class, 'dept_id', 'id');
    }
    public function employees()
    {
        return $this->hasMany(Employee::class, 'designation_id');
    }
}
