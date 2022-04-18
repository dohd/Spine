<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChargesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('charges', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('tid')->default(0);
            $table->integer('bank_id')->default(0);
            $table->integer('expense_id')->default(0);
            $table->decimal('amount', 16, 4)->default(0);
            $table->string('payment_mode')->nullable();
            $table->date('date');
            $table->string('reference')->nullable();
            $table->string('note')->nullable();
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
        Schema::dropIfExists('charges');
    }
}
