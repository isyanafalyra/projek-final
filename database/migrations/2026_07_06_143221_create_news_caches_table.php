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
        Schema::create('news_caches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('title', 500);
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->string('source_name')->nullable();
            $table->text('url');
            $table->timestamp('published_at')->nullable();
            $table->string('sentiment')->default('Neutral'); // Positive, Neutral, Negative
            $table->integer('sentiment_score_positive')->default(0);
            $table->integer('sentiment_score_negative')->default(0);
            $table->integer('sentiment_score_neutral')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_caches');
    }
};
