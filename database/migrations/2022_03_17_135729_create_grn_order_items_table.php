<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGrnOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grn_order_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('grnorder_id')->unsigned();
            $table->integer('item_id');
            $table->string('dnote');
            $table->string('description')->nullable();
            $table->string('uom')->nullable();
            $table->integer('itemproject_id')->default(0);
            $table->decimal('qty', 16, 4);
            $table->decimal('rate', 16, 4);
            $table->decimal('taxrate', 16, 4);
            $table->decimal('itemtax', 16, 4)->default(0.0000);
            $table->decimal('amount', 16, 4);
            $table->enum('type', ['Stock', 'Expense', 'Asset']);
            $table->integer('ins')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            // 
            $table->foreign('grnorder_id')->references('id')->on('grn_orders')->onDelete('CASCADE');
            $table->foreign('ins')->references('id')->on('companies')->onDelete('CASCADE');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('grn_order_items');
    }
}