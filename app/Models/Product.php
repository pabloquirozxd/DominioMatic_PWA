<?php

namespace App\Models;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use Tenantable;

    protected $fillable = ['company_id', 'name', 'description', 'price_list', 'is_infinite', 'stock'];
}