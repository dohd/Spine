<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreditNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('tid');
            $table->integer('invoice_id')->default(0);
            $table->integer('customer_id')->default(0);
            $table->string('note');
            $table->decimal('subtotal', 16, 4);
            $table->decimal('tax', 16, 4);
            $table->decimal('total', 16, 4);
            $table->date('date')->default('0000-00-00');
            $table->integer('is_debit')->default(0);
            $table->integer('ins')->default(0);
            $table->integer('user_id')->default(0);
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
        Schema::dropIfExists('credit_notes');
    }
}
