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
        $table->foreignId('company_id')->constrained()->onDelete('cascade');
        $table->foreignId('contact_id')->constrained()->onDelete('cascade'); // El cliente/contacto
        $table->foreignId('product_id')->constrained()->onDelete('cascade'); // El servicio contratado

        // Cálculo Algebraico Automatizado (Req. 2 de tu informe)
        $table->decimal('price_list', 10, 2);
        $table->decimal('discount', 10, 2)->default(0.00); // Monto fijo o calculado de descuento
        $table->decimal('total_neto', 10, 2); // Precio Final: (price_list - discount)

        // Control Cronológico de Alertas Preventivas
        $table->date('starts_at');
        $table->date('expires_at'); // Fecha crítica para las Alertas Automatizadas
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
