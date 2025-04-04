<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('product_leads', function (Blueprint $table) {
            $table->string('customer_email')->nullable()->after('postcode');
            $table->string('customer_name')->nullable()->after('customer_email');
            $table->string('lead_type')->default('order')->after('customer_name');
        });
    }

    public function down()
    {
        Schema::table('product_leads', function (Blueprint $table) {
            $table->dropColumn(['customer_email', 'customer_name', 'lead_type']);
        });
    }
};
