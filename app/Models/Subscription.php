<?php

namespace App\Models;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use Tenantable;

    protected $fillable = [
        'company_id',
        'contact_id',
        'product_id',
        'price_list',
        'currency',
        'discount',
        'total_neto',
        'starts_at',
        'expires_at',
        'status',
    ];

    protected $casts = [
        'price_list' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_neto' => 'decimal:2',
        'starts_at' => 'date',
        'expires_at' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($subscription) {
            $subscription->total_neto =
                $subscription->price_list - $subscription->discount;
        });
    }

    /**
     * Estado real de la suscripción según la fecha.
     *
     * suspended tiene prioridad porque es un estado manual.
     * Si no está suspendida y ya pasó expires_at, está vencida.
     */
    public function getEffectiveStatusAttribute(): string
    {
        if ($this->status === 'suspended') {
            return 'suspended';
        }

        if (
            $this->expires_at &&
            $this->expires_at->startOfDay()->isBefore(
                now()->startOfDay()
            )
        ) {
            return 'expired';
        }

        return 'active';
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(SubscriptionValue::class);
    }
}