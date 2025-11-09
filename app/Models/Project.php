<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }
}
