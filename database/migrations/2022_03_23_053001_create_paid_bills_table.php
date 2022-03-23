<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaidBillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paid_bills', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('supplier_id')->unsigned();
            $table->integer('tid');
            $table->date('date');
            $table->date('due_date');
            $table->string('payment_mode')->nullable();
            $table->string('doc_ref_type')->nullable();
            $table->string('doc_ref')->nullable();
            $table->decimal('deposit', 16, 4);
            $table->decimal('amount_ttl', 16, 4);
            $table->decimal('deposit_ttl', 16, 4);
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
        Schema::dropIfExists('paid_bills');
    }
}
