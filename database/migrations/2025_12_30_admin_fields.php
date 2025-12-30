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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('email');
            }
            if (!Schema::hasColumn('users', 'is_banned')) {
                $table->boolean('is_banned')->default(false)->after('is_active');
            }
            if (!Schema::hasColumn('users', 'last_login')) {
                $table->timestamp('last_login')->nullable()->after('is_banned');
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'bio')) {
                $table->text('bio')->nullable()->after('avatar');
            }
        });

        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasColumn('posts', 'is_flagged')) {
                $table->boolean('is_flagged')->default(false)->after('content');
            }
            if (!Schema::hasColumn('posts', 'flag_reason')) {
                $table->string('flag_reason')->nullable()->after('is_flagged');
            }
            if (!Schema::hasColumn('posts', 'flagged_at')) {
                $table->timestamp('flagged_at')->nullable()->after('flag_reason');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumnIfExists(['is_active', 'is_banned', 'last_login', 'phone', 'avatar', 'bio']);
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumnIfExists(['is_flagged', 'flag_reason', 'flagged_at']);
        });
    }
};
