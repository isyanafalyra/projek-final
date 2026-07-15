<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ports', function (Blueprint $table) {
            $table->string('country_code', 2)->nullable()->after('country_id');
            $table->string('city')->nullable()->after('country_code');
            $table->string('status')->default('active')->after('code');
            $table->string('type')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ports', function (Blueprint $table) {
            $table->dropColumn(['country_code', 'city', 'status', 'type']);
        });
    }
};
