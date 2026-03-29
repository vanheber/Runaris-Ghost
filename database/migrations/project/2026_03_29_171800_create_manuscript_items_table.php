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
        Schema::create('manuscript_items', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->uuid('uuid')->unique();
            $blueprint->uuid('parent_uuid')->nullable();
            $blueprint->string('type'); // section, chapter, scene
            $blueprint->string('title');
            $blueprint->integer('order')->default(0);
            $blueprint->timestamp('content_updated_at')->nullable();
            $blueprint->integer('word_count')->default(0);
            $blueprint->timestamps();
            
            $blueprint->index(['parent_uuid', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manuscript_items');
    }
};
