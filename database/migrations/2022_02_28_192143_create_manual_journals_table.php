<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManualJournalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manual_journals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('account_id');
            $table->integer('from_account_id');
            $table->date('date');
            $table->decimal('amount', 16, 4);
            $table->string('transaction_ref');
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
        Schema::dropIfExists('manual_journals');
    }
}
