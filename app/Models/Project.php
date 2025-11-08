<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'status',
        'priority',
        'type',
        'recurring',
        'budget',
        'created_by'
    ];
}
