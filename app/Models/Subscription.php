<?php

namespace App\Models;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use Tenantable; // Candado SaaS activo

    // Permitimos la carga masiva de los campos críticos
    protected $fillable = [
        'company_id',
        'contact_id',
        'product_id',
        'price_list',
        'discount',
        'total_neto',
        'starts_at',
        'expires_at',
        'status'
    ];

    // Forzamos a Laravel a tratar las fechas y números con sus tipos de datos reales
    protected $casts = [
        'price_list' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_neto' => 'decimal:2',
        'starts_at' => 'date',
        'expires_at' => 'date',
    ];

    /**
     * Boot del modelo: Aquí automatizamos el cálculo algebraico del reporte
     */
    protected static function boot()
    {
        parent::boot();

        // Antes de guardar (crear o actualizar), calculamos el neto de forma segura
        static::saving(function ($subscription) {
            $subscription->total_neto = $subscription->price_list - $subscription->discount;
        });
    }

    /**
     * Relación con el Cliente/Contacto
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * Relación con el Producto/Servicio base
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relación con los valores dinámicos (EAV)
     */
    public function values(): HasMany
    {
        return $this->hasMany(SubscriptionValue::class);
    }
}