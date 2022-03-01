<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterSuppliersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('account_no')->nullable();
            $table->string('account_name')->nullable();
            $table->integer('payment_terms')->nullable();
            $table->decimal('credit_limit', 16, 4)->nullable();
            $table->string('mpesa_payment')->nullable();
            $table->decimal('opening_balance', 16, 4)->nullable();
             $table->date('opening_balance_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('account_no');
            $table->dropColumn('account_name');
            $table->dropColumn('payment_terms');
            $table->dropColumn('credit_limit');
            $table->dropColumn('opening_balance');
            $table->dropColumn('opening_balance_date');
        });
    }
}
