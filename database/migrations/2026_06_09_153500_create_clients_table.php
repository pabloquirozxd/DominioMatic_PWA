<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            // Empresa o Persona
            $table->enum('type', [
                'company',
                'person'
            ]);

            // Solo si es empresa
            $table->string('company_name')->nullable();

            // NUEVOS CAMPOS (Estilo Zoho)
            $table->string('company_phone')->nullable(); // Teléfono fijo/empresarial
            $table->string('tax_id')->nullable();        // NIT / ID Impuestos
            $table->string('payment_terms')->default('Due on Receipt'); // Términos de Pago
            $table->text('notes')->nullable();           // Notas internas / Remarks

            // Idioma principal
            $table->string('language')->default('Español');

            // Sitio web
            $table->string('website')->nullable();

            // Portal del cliente
            $table->boolean('portal_enabled')->default(false);

            // Empresa propietaria (multi tenant)
            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};