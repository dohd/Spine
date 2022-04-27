<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGrnItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grn_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('grn_id')->unsigned();
            $table->bigInteger('poitem_id')->unsigned();
            $table->decimal('qty', 16, 4);
            $table->string('dnote');
            $table->date('date');
            $table->integer('ins')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            // 
            $table->foreign('grn_id')->references('id')->on('grn')->onDelete('CASCADE');
            $table->foreign('poitem_id')->references('id')->on('purchase_order_items')->onDelete('CASCADE');
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
        Schema::dropIfExists('grn_items');
    }
}
