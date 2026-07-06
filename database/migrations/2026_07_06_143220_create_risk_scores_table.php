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
        Schema::create('risk_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->onDelete('cascade');
            $table->decimal('weather_score', 5, 2);
            $table->decimal('inflation_score', 5, 2);
            $table->decimal('political_score', 5, 2);
            $table->decimal('currency_score', 5, 2);
            $table->decimal('total_score', 5, 2);
            $table->string('risk_level'); // e.g. Low Risk, Medium Risk, High Risk
            $table->timestamp('calculated_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_scores');
    }
};
