<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'description',
        'department_id',
        'start_date',
        'end_date',
        'created_by',
        'announcement_type',
        'is_active',
        'audience',
        'attachment',
    ];


    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }


}

