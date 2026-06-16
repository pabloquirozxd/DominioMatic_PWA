<?php

namespace App\Models;

use App\Traits\Tenantable; // Importamos el trait
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use Tenantable; // Activamos el candado

    protected $fillable = ['company_id', 'first_name', 'last_name', 'email', 'phone', 'type', 'position'];
}