<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bill_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('item_id')->nullable();
            $table->text('description')->nullable();
            $table->integer('item_project_id')->nullable();
            $table->bigInteger('bills_id')->length(20)->unsigned(); 
            $table->decimal('qty', 16, 4)->default('0.0000');
            $table->decimal('rate', 16, 4)->nullable();
            $table->decimal('tax_rate', 16, 4)->default('0.0000');
            $table->decimal('tax_amount', 16, 4)->nullable();
            $table->decimal('amount', 16, 4)->nullable();
            $table->integer('ins')->length(10)->unsigned(); 
            $table->integer('user_id')->length(10)->unsigned();
            $table->enum('item_type', ['Stock', 'Expense', 'Asset'])->default('Expense');
            $table->index('bills_id');
            $table->index('ins');
            $table->index('user_id');
            $table->foreign('bills_id')->references('id')->on('bills')->onDelete('cascade');
            $table->foreign('ins')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bill_items');
    }
}
