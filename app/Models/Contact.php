<?php

namespace App\Models;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory, Tenantable;

    protected $fillable = [
        'company_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'position',
    ];

    /**
     * Devuelve dinámicamente 'primary' o 'secondary' según el estado de is_primary en el pivote.
     */
    protected function type(): Attribute
    {
        return Attribute::make(
            get: fn () => (!empty($this->pivot) && $this->pivot->is_primary) ? 'primary' : 'secondary',
        );
    }

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'client_contact')
                    ->withPivot('position', 'is_primary') // 👈 Removido 'type' del pivote
                    ->withTimestamps();
    }
}   