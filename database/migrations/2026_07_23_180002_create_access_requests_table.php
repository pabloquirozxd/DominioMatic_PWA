<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            
            $table->string('name');
            $table->string('email');
            $table->string('password')->nullable(); // <-- AÑADIDO PARA GUARDAR LA CONTRASEÑA
            $table->text('message')->nullable();
            
            // Estados permitidos: pending, approved, rejected, cancelled, expired
            $table->string('status')->default('pending');
            
            // Auditoría e Historial
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            $table->timestamps();

            // Índice para optimizar búsqueda de duplicados
            $table->index(['company_id', 'email', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_requests');
    }
};