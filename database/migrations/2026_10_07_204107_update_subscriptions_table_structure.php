<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Asegurar que billing_cycle acepte todos los valores del modal
            $table->string('billing_cycle')->default('monthly')->change();
            
            // Añadir campos numéricos si no existen
            if (!Schema::hasColumn('subscriptions', 'quantity')) {
                $table->integer('quantity')->default(1);
            }
            if (!Schema::hasColumn('subscriptions', 'price_list')) {
                $table->decimal('price_list', 12, 2)->default(0.00);
            }
            if (!Schema::hasColumn('subscriptions', 'currency')) {
                $table->string('currency', 3)->default('USD');
            }
            if (!Schema::hasColumn('subscriptions', 'discount')) {
                $table->decimal('discount', 12, 2)->default(0.00);
            }
            if (!Schema::hasColumn('subscriptions', 'total_neto')) {
                $table->decimal('total_neto', 12, 2)->default(0.00);
            }
        });
    }

    public function down(): void
    {
        //
    }
};