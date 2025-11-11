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
        Schema::create('quantity_adjustments', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date');
            $table->string('reference_no')->nullable();
            $table->string('document')->nullable(); // File path for attachment
            $table->text('note')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // User who created the adjustment
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quantity_adjustments');
    }
};
