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
        Schema::create('card_connections', function (Blueprint $table) {
            $table->id();
            $table->string('card_uuid');
            $table->string('related_card_uuid');
            $table->json('metadata')->nullable(); // For relationship types
            $table->timestamps();

            $table->foreign('card_uuid')->references('uuid')->on('cards')->onDelete('cascade');
            $table->foreign('related_card_uuid')->references('uuid')->on('cards')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_connections');
    }
};
