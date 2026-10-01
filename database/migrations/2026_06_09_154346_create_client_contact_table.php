<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_contact', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();

            // El cargo pertenece a la relación con la empresa
            $table->string('position')->nullable();
            
            // Indicador único de contacto principal (evita redundancia con 'type')
            $table->boolean('is_primary')->default(false);

            $table->timestamps();

            // Un mismo contacto no se asigna dos veces a la misma empresa
            $table->unique(['client_id', 'contact_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_contact');
    }
};