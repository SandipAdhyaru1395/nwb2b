<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::table('products', function (Blueprint $table) {
      $table->string('product_unit_sku')->nullable()->unique()->after('sku');
      $table->decimal('weight', 10, 2)->nullable()->after('wallet_credit');
      $table->decimal('rrp', 10, 2)->nullable()->after('weight');
      $table->date('expiry_date')->nullable()->after('rrp');
    });
  }

  public function down(): void
  {
    Schema::table('products', function (Blueprint $table) {
      $table->dropUnique(['product_unit_sku']);
      $table->dropColumn(['product_unit_sku','weight','rrp','expiry_date']);
    });
  }
};


