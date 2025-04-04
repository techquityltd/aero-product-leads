<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('product_leads', function (Blueprint $table) {
            // Add a new nullable variant_id column
            $table->unsignedBigInteger('variant_id')->nullable()->after('order_item_id');

            // Add a foreign key constraint to the variants table
            $table->foreign('variant_id')->references('id')->on('variants')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('product_leads', function (Blueprint $table) {
            // Rollback by dropping variant_id
            $table->dropForeign(['variant_id']);
            $table->dropColumn('variant_id');
        });
    }
};
