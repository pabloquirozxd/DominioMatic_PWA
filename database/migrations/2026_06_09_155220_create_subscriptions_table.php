<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();

            // Empresa propietaria / Multi-Tenant
            $table->foreignId('company_id')->constrained()->onDelete('cascade');

            // Cliente (Empresa) vinculado
            $table->foreignId('client_id')->constrained()->onDelete('cascade');

            // Servicio o Producto contratado
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            // Cantidad y Frecuencia de Cobro
            $table->integer('quantity')->default(1);
            $table->enum('billing_cycle', ['weekly', 'monthly', 'yearly', 'custom'])->default('monthly');

            // Cálculos y Moneda
            $table->decimal('price_list', 10, 2);
            $table->string('currency', 10)->default('USD');
            $table->decimal('discount', 10, 2)->default(0.00);
            $table->decimal('total_neto', 10, 2); // (price_list * quantity) - discount

            // Control Cronológico
            $table->date('starts_at');
            $table->date('expires_at');
            $table->enum('status', ['active', 'expired', 'suspended'])->default('active');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};