<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LateType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];
}
