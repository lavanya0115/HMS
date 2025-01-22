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
        Schema::table('menu_items', function (Blueprint $table) {
            if (!Schema::hasColumn('menu_items', 'unit_type')) {
                $table->string('unit_type')->nullable()->after('nos');
            }
            if (!Schema::hasColumn('menu_items', 'kannada_name')) {
                $table->string('kannada_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('menu_items', 'tax')) {
                $table->string('tax')->nullable()->after('price');
            }
            if (!Schema::hasColumn('menu_items', 'tax_amount')) {
                $table->string('tax_amount')->nullable()->after('tax');
            }
            if (!Schema::hasColumn('menu_items', 'mrp')) {
                $table->string('mrp')->nullable()->after('tax_amount');
            }
            if (!Schema::hasColumn('menu_items', 'custom_status')) {
                $table->string('custom_status')->nullable()->after('is_available');
            }
            if (Schema::hasColumn('menu_items', 'nos')) {
                $table->renameColumn('nos', 'qty');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            if (Schema::hasColumn('menu_items', 'unit_type')) {
                $table->dropColumn('unit_type');
            }
            if (Schema::hasColumn('menu_items', 'tax')) {
                $table->dropColumn('tax');
            }
            if (Schema::hasColumn('menu_items', 'tax_amount')) {
                $table->dropColumn('tax_amount');
            }
            if (Schema::hasColumn('menu_items', 'mrp')) {
                $table->dropColumn('mrp');
            }
            if (Schema::hasColumn('menu_items', 'custom_status')) {
                $table->dropColumn('custom_status');
            }
            if (Schema::hasColumn('menu_items', 'qty')) {
                $table->renameColumn('qty', 'nos');
            }
        });
    }
};
