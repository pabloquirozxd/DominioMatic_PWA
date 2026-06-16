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
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->foreignId('company_id')->constrained()->onDelete('cascade');

        $table->string('name'); // Ej: "Hosting Corporativo Plan A" o "Dominio .bo"
        $table->text('description')->nullable();
        $table->decimal('price_list', 10, 2); // Precio de lista base

        // Control Híbrido de Inventario
        $table->boolean('is_infinite')->default(true); // true = Hosting/Cloud, false = Finito/Hardware
        $table->integer('stock')->default(0); // Solo aplica si is_infinite es false

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
