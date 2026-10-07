<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Si existe contact_id y no client_id, renombrar o crear client_id
            if (Schema::hasColumn('subscriptions', 'contact_id') && !Schema::hasColumn('subscriptions', 'client_id')) {
                $table->renameColumn('contact_id', 'client_id');
            } elseif (!Schema::hasColumn('subscriptions', 'client_id')) {
                $table->foreignId('client_id')->nullable()->after('company_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('subscriptions', 'client_id')) {
                $table->renameColumn('client_id', 'contact_id');
            }
        });
    }
};