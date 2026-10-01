<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Tenantable
{
    /**
     * Filtra automáticamente los registros por la empresa activa.
     */
    protected static function bootTenantable(): void
    {
        static::addGlobalScope('company_scope', function (Builder $builder) {
            $companyId = static::getTenantCompanyId();

            if ($companyId) {
                $builder->where(
                    $builder->getModel()->getTable() . '.company_id',
                    $companyId
                );
            }
        });

        /**
         * Al crear un registro, asignamos automáticamente la empresa activa.
         */
        static::creating(function ($model) {
            if (empty($model->company_id)) {
                $model->company_id = static::getTenantCompanyId();
            }
        });
    }

    /**
     * Obtiene de forma robusta el ID de la empresa activa del entorno actual.
     */
    protected static function getTenantCompanyId(): ?int
    {
        // 1. Prioridad: Valor guardado explícitamente en la sesión HTTP
        if (session()->has('active_company_id')) {
            return session('active_company_id');
        }

        // 2. Fallback: Si no hay sesión (o no se ha seteado), buscar la primera empresa del usuario logueado
        if (auth()->check()) {
            $user = auth()->user();
            return $user->companies()->first()?->id;
        }

        return null;
    }
}