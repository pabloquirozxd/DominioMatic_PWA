<?php

namespace App\Models;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionAttribute extends Model
{
    use Tenantable;
    protected $fillable = ['company_id', 'name', 'type'];

    public function values(): HasMany
    {
        return $this->hasMany(SubscriptionValue::class);
    }
}