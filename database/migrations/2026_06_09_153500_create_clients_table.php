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

            // Empresa propietaria (Multi-Tenant)
            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            // Tipo de cuenta (Empresa o Persona)
            $table->enum('type', [
                'company',
                'person'
            ])->default('company');

            // Nombre de la Empresa o Nombre Comercial
            $table->string('company_name')->nullable();

            // Datos fiscales y de contacto empresarial
            $table->string('company_phone')->nullable(); // Teléfono fijo / empresarial
            $table->string('tax_id')->nullable();        // NIT / ID Impuestos
            $table->string('website')->nullable();       // Sitio web oficial

            // Configuración comercial
            $table->string('language')->default('Español');
            $table->string('payment_terms')->default('Due on Receipt');
            $table->text('notes')->nullable();           // Notas internas

            // Acceso
            $table->boolean('portal_enabled')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};