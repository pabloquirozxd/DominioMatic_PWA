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

        $table->string('type')->default('service'); // 'service' o 'product'
        $table->string('name');
        $table->text('description')->nullable();
        $table->decimal('price_list', 10, 2);

        // Control Híbrido de Inventario
        $table->boolean('is_infinite')->default(true);
        $table->integer('stock')->default(0);

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
