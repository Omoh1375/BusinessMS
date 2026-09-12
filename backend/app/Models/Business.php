<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Business extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'legal_name',
        'business_type',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'currency',
        'timezone',
        'tax_identification_number',
        'logo',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot([
                'role',
                'status',
                'joined_at',
            ])
            ->withTimestamps();
    }

    
}