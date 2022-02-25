<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccountAndSubAccountColumnOnTransactionCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transaction_categories', function (Blueprint $table) {
            $table->string('account');
            $table->string('sub_account');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transaction_categories', function (Blueprint $table) {
            $table->dropColumn('account');
            $table->dropColumn('sub_account');
        });
    }
}
