<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('product_leads', function (Blueprint $table) {
            $table->string('customer_phone')->nullable()->after('customer_name');
        });
    }

    public function down()
    {
        Schema::table('product_leads', function (Blueprint $table) {
            $table->dropColumn('customer_phone');
        });
    }
};
