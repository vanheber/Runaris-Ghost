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
        Schema::table('projects', function (Blueprint $table) {
            $table->string('author')->nullable();
            $table->string('isbn')->nullable();
            $table->string('language', 10)->default('pt-BR');
            $table->string('publisher')->nullable();
            $table->string('publication_date')->nullable();
            $table->text('copyright_info')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['author', 'isbn', 'language', 'publisher', 'publication_date', 'copyright_info']);
        });
    }
};
