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
        // Modify status column to include 'Returned' in enum
        // This will work whether status is currently ENUM or VARCHAR
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('Completed', 'Returned') NOT NULL DEFAULT 'Completed'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'Returned' from enum, keeping only 'Completed'
        // Note: This assumes all 'Returned' records are updated before rollback
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('Completed') NOT NULL DEFAULT 'Completed'");
    }
};
