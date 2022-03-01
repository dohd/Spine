<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('transaction_ref');
            $table->string('document_ref_type');
            $table->string('document_ref');
            $table->date('date');
            $table->date('due_date');
            $table->enum('supplier_type', ['walk-in', 'supplier'])->default('supplier');
            $table->integer('supplier_id')->nullable();
            $table->integer('supplier_name')->nullable();
            $table->integer('supplier_vat_pin')->nullable();
            $table->integer('project_id')->nullable();
            $table->decimal('stock_subtotal_amount', 16, 4)->nullable();
            $table->decimal('stock_tax_amount', 16, 4)->nullable();
            $table->decimal('stock_grandtotal_amount', 16, 4)->nullable();
            $table->decimal('expense_subtotal_amount', 16, 4)->nullable();
            $table->decimal('expense_tax_amount', 16, 4)->nullable();
            $table->decimal('expense_grandtotal_amount', 16, 4)->nullable();
            $table->decimal('asset_subtotal_amount', 16, 4)->nullable();
            $table->decimal('asset_tax_amount', 16, 4)->nullable();
            $table->decimal('asset_grandtotal_amount', 16, 4)->nullable();
            $table->decimal('grand_tax_amount', 16, 4)->nullable();
            $table->decimal('grand_total_amount', 16, 4);
            $table->enum('payment_status', ['Pending', 'Partial', 'Paid','Cancelled'])->default('Pending');
            $table->decimal('total_amount_paid', 16, 4)->nullable();
            $table->text('note');
            $table->integer('ins')->length(10)->unsigned(); 
            $table->integer('user_id')->length(10)->unsigned();
            $table->index('ins');
            $table->index('user_id');
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
        Schema::dropIfExists('bills');
    }
}
