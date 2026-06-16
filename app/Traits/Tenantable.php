<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait Tenantable
{
    /**
     * El método bootTrait se ejecuta automáticamente cuando el modelo se inicializa.
     */
    protected static function bootTenantable(): void
    {
        // 1. Candado de Lectura: Si hay un usuario logueado, solo ve los datos de su empresa
        if (Auth::check() && Auth::user()->company_id) {
            static::addGlobalScope('company_scope', function (Builder $builder) {
                $builder->where('company_id', Auth::user()->company_id);
            });
        }

        // 2. Automatización de Escritura: Al crear un registro, se le asigna su company_id de forma invisible
        static::creating(function ($model) {
            if (Auth::check() && Auth::user()->company_id) {
                $model->company_id = Auth::user()->company_id;
            }
        });
    }
}