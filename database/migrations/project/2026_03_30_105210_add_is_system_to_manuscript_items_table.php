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
        Schema::table('manuscript_items', function (Blueprint $table) {
            $table->boolean('is_system')->default(false)->after('word_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manuscript_items', function (Blueprint $table) {
            $table->dropColumn('is_system');
        });
    }
};
