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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_code')->unique(); 
            $table->string('invitation_id');
            $table->string('event_name');
            $table->datetime('event_date');
            $table->string('sector');
            $table->boolean('is_validated')->default(false);
            $table->datetime('validated_at')->nullable();
            $table->unsignedBigInteger('validated_by')->nullable();
            $table->string('validation_ip')->nullable();
            $table->string('redeemed_ip')->nullable();
            $table->timestamps();
            
            $table->foreign('validated_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['ticket_code']);
            $table->index(['invitation_id']);
            $table->index(['is_validated']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
