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
        Schema::connection('sqlite_project')->table('manuscript_items', function (Blueprint $table) {
            $table->boolean('has_planning')->default(false);
            $table->timestamp('planning_updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('sqlite_project')->table('manuscript_items', function (Blueprint $table) {
            $table->dropColumn(['has_planning', 'planning_updated_at']);
        });
    }
};
