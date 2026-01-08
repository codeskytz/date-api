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
        Schema::table('posts', function (Blueprint $table) {
            // Increase URL column lengths to accommodate presigned URLs (400+ chars)
            $table->string('image', 1000)->nullable()->change();
            $table->string('video', 1000)->nullable()->change();
            $table->string('thumbnail', 1000)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Revert to original lengths
            $table->string('image', 255)->nullable()->change();
            $table->string('video', 255)->nullable()->change();
            $table->string('thumbnail', 255)->nullable()->change();
        });
    }
};
