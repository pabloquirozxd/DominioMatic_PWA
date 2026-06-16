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
    Schema::create('contacts', function (Blueprint $table) {
        $table->id();
        // Aislamiento SaaS: El contacto pertenece a un inquilino (Company)
        $table->foreignId('company_id')->constrained()->onDelete('cascade');

        // Datos del contacto empresarial
        $table->string('first_name');
        $table->string('last_name');
        $table->string('email')->nullable();
        $table->string('phone')->nullable();

        // Relaciones pivote
        $table->enum('type', ['primary', 'secondary'])->default('secondary');
        $table->string('position')->nullable(); // Ej: "Gerente de Sistemas"

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
