<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attachment extends Model
{
    protected $fillable = [
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'attachable_id',
        'attachable_type',
    ];

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}
