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
        Schema::table('stories', function (Blueprint $table) {
            $table->string('video')->nullable()->after('image')->comment('Video file URL on S3');
            $table->string('media_type')->default('image')->after('video')->comment('image or video');
            $table->integer('video_duration')->nullable()->after('media_type')->comment('Duration in seconds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stories', function (Blueprint $table) {
            $table->dropColumn(['video', 'media_type', 'video_duration']);
        });
    }
};
