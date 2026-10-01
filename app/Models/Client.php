<?php

namespace App\Models;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory, Tenantable;

    protected $fillable = [
        'company_id',
        'type',
        'company_name',
        'phone',
        'company_phone',
        'tax_id',
        'payment_terms',
        'notes',
        'website',
        'language',
        'portal_enabled',
    ];

    protected $casts = [
        'portal_enabled' => 'boolean',
    ];

    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'client_contact')
                    ->withPivot('position', 'is_primary')
                    ->withTimestamps();
    }
}