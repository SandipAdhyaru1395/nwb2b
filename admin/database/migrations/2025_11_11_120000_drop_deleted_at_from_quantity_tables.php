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
        // Drop deleted_at from quantity_adjustments if exists
        Schema::table('quantity_adjustments', function (Blueprint $table) {
            if (Schema::hasColumn('quantity_adjustments', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        // Drop deleted_at from quantity_adjustment_items if exists
        Schema::table('quantity_adjustment_items', function (Blueprint $table) {
            if (Schema::hasColumn('quantity_adjustment_items', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add deleted_at to quantity_adjustments
        Schema::table('quantity_adjustments', function (Blueprint $table) {
            if (!Schema::hasColumn('quantity_adjustments', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Re-add deleted_at to quantity_adjustment_items
        Schema::table('quantity_adjustment_items', function (Blueprint $table) {
            if (!Schema::hasColumn('quantity_adjustment_items', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }
};


