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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('title', 500);
            $table->string('slug', 500)->unique();
            $table->text('description');
            $table->unsignedBigInteger('goal_amount_cents');
            $table->unsignedBigInteger('current_amount_cents')->default(0);
            $table->foreignId('creator_id')->constrained('users');
            $table->foreignId('category_id')->nullable()->constrained('categories');
            $table->string('status', 50)->default('active');
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->string('featured_image_url', 500)->nullable();
            $table->integer('donations_count')->default(0);
            $table->integer('donors_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('creator_id');
            $table->index('category_id');
            $table->index('status');
            $table->index(['start_date', 'end_date']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
