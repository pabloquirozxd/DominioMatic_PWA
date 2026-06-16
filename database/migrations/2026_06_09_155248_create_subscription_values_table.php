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
    Schema::create('subscription_values', function (Blueprint $table) {
        $table->id();
        $table->foreignId('subscription_id')->constrained()->onDelete('cascade');
        $table->foreignId('subscription_attribute_id')->constrained()->onDelete('cascade');
        $table->text('value'); // Ej: "192.168.1.1" o "admin_root"
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_values');
    }
};
