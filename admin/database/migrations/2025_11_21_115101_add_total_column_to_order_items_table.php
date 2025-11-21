<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('total', 10, 2)->nullable()->after('total_vat');
        });
        
        // Calculate and populate total for existing records
        DB::statement('UPDATE order_items SET total = COALESCE(total_price, 0) + COALESCE(total_vat, 0) WHERE total IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('total');
        });
    }
};
