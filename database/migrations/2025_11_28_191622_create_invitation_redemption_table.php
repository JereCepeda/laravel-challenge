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
        Schema::create('invitation_redemptions', function (Blueprint $table) {
            $table->id();

            $table->timestamp('redeemed_at')->useCurrent();
            $table->string('invitation_id')->unique();
            $table->string('event_name');
            $table->datetime('event_date');
            $table->string('sector');
            $table->integer('guest_count');
            $table->integer('tickets_generated');
            $table->string('redeemed_ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
