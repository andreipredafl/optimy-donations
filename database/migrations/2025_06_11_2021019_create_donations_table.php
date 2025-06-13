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
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns');
            $table->foreignId('user_id')->constrained('users');
            $table->unsignedBigInteger('amount_cents');
            $table->string('payment_method', 50);
            $table->string('transaction_id')->unique();
            $table->string('gateway_transaction_id')->nullable();
            $table->string('status', 50)->default('pending');
            $table->boolean('is_anonymous')->default(false);
            $table->text('message')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('campaign_id');
            $table->index('user_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
