<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('tid');
            $table->date('date');
            $table->date('due_date');
            $table->integer('supplier_id');
            $table->integer('tax');
            $table->integer('project_id');
            $table->decimal('stock_subttl', 16, 4);
            $table->decimal('stock_tax', 16, 4);
            $table->decimal('stock_grandttl', 16, 4);
            $table->decimal('expense_subttl', 16, 4);
            $table->decimal('expense_tax', 16, 4);
            $table->decimal('expense_grandttl', 16, 4);
            $table->decimal('asset_subttl', 16, 4);
            $table->decimal('asset_tax', 16, 4);
            $table->decimal('asset_grandttl', 16, 4);
            $table->decimal('grandtax', 16, 4);
            $table->decimal('grandttl', 16, 4);
            $table->decimal('paidttl', 16, 4);
            $table->string('status')->default('pending');
            $table->integer('note')->nullable();
            $table->integer('doc_ref_type')->nullable();
            $table->integer('doc_ref')->nullable();
            $table->integer('ins')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_orders');
    }
}
