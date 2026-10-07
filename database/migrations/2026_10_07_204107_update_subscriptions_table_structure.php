<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Asegurar la presencia de client_id
            if (!Schema::hasColumn('subscriptions', 'client_id')) {
                $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('cascade')->after('company_id');
            }

            // Si la columna billing_cycle existe, se modifica; si no existe, se crea.
            if (Schema::hasColumn('subscriptions', 'billing_cycle')) {
                $table->string('billing_cycle')->default('monthly')->change();
            } else {
                $table->string('billing_cycle')->default('monthly')->after('product_id');
            }

            if (!Schema::hasColumn('subscriptions', 'quantity')) {
                $table->integer('quantity')->default(1)->after('product_id');
            }
            if (!Schema::hasColumn('subscriptions', 'price_list')) {
                $table->decimal('price_list', 12, 2)->default(0.00)->after('quantity');
            }
            if (!Schema::hasColumn('subscriptions', 'currency')) {
                $table->string('currency', 3)->default('USD')->after('price_list');
            }
            if (!Schema::hasColumn('subscriptions', 'discount')) {
                $table->decimal('discount', 12, 2)->default(0.00)->after('currency');
            }
            if (!Schema::hasColumn('subscriptions', 'total_neto')) {
                $table->decimal('total_neto', 12, 2)->default(0.00)->after('discount');
            }
        });
    }

    public function down(): void
    {
        //
    }
};