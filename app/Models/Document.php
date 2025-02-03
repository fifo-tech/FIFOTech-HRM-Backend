<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{

    protected $fillable = [
        'employee_id', // Add this field
        'doc_name',
        'doc_type',
        'doc_file',
    ];



    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

}
