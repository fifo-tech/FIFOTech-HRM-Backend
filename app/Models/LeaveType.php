<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{

    protected $fillable = ['name', 'days_per_year', 'requires_approval'];
    protected $casts = [
        'requires_approval' => 'boolean',
    ];

}
