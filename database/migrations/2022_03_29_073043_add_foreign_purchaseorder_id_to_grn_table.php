<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignPurchaseorderIdToGrnTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('grn', function (Blueprint $table) {
            $table->foreign('purchaseorder_id')->references('id')->on('purchase_orders')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('grn', function (Blueprint $table) {
            //
        });
    }
}
