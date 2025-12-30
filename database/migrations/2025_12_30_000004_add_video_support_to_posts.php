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
            $table->string('image')->nullable()->comment('Image file URL on S3');
            $table->string('video')->nullable()->comment('Video file URL on S3');
            $table->string('video_status')->default('pending')->comment('pending, processing, ready, failed');
            $table->integer('video_duration')->nullable()->comment('Duration in seconds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['image', 'video', 'video_status', 'video_duration']);
        });
    }
};
