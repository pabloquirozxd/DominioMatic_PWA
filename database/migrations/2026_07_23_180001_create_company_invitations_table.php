<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_invitations', function (Blueprint $table) {
            $table->uuid('id')->primary(); // La invitación sí usa UUID como ID propio
            $table->foreignId('company_id')->constrained()->cascadeOnDelete(); // Entérate: usa foreignId (bigint) para la relación
            $table->string('email');
            $table->string('role')->default('Member');
            $table->string('token', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_invitations');
    }
};