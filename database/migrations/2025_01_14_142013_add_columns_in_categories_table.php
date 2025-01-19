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
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'type')) {
                $table->string('type')->nullable()->after('title');
            }
            if (!Schema::hasColumn('categories', 'day')) {
                $table->string('day')->nullable()->after('type');
            }
            if (!Schema::hasColumn('categories', 'show_time_from')) {
                $table->string('show_time_from')->nullable()->after('type');
            }
            if (!Schema::hasColumn('categories', 'show_time_to')) {
                $table->string('show_time_to')->nullable()->after('show_time_from');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('categories', 'day')) {
                $table->dropColumn('day');
            }
            if (Schema::hasColumn('categories', 'show_time_from')) {
                $table->dropColumn('show_time_from');
            }
            if (Schema::hasColumn('categories', 'show_time_to')) {
                $table->dropColumn('show_time_to');
            }
        });
    }
};
