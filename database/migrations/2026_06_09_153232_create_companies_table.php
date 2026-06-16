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
    Schema::create('companies', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // Ejemplo: "DominioMatic"
        $table->string('slug')->unique(); // Ejemplo: "dominiomatic"
        $table->string('logo_path')->nullable(); // Para Branding personalizado
        $table->string('primary_color')->default('#007AFF'); // Estilo Apple
        $table->string('secondary_color')->default('#5856D6');
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
