<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    // Define the relationship with the Designation model
    public function designations()
    {
        return $this->hasMany(Designation::class, 'dept_id', 'id');
    }
    /**
     * A Department has many Employees.
     */
    public function employees()
    {
        return $this->hasMany(Employee::class, 'dept_id');
    }
}
