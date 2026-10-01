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
        'client_id',
        'product_id',
        'quantity',
        'billing_cycle',
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
        'quantity' => 'integer',
        'starts_at' => 'date',
        'expires_at' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($subscription) {
            $price = (float) ($subscription->price_list ?? 0);
            $qty = (int) ($subscription->quantity ?? 1);
            $discount = (float) ($subscription->discount ?? 0);

            // Total Neto = (Precio Unitario x Cantidad) - Descuento
            $subscription->total_neto = max(0, ($price * $qty) - $discount);
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

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
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