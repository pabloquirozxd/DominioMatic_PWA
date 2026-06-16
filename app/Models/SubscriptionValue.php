<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionValue extends Model
{
    protected $fillable = ['subscription_id', 'subscription_attribute_id', 'value'];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(SubscriptionAttribute::class, 'subscription_attribute_id');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}