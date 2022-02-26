<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
          
            $table->integer('category_id')->default(0);
            $table->decimal('opening_balance', 16, 4);
            $table->string('system')->nulable();
            $table->date('opening_balance_date')->nulable();
            $table->dropColumn('code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('category_id');
            $table->dropColumn('opening_balance');
            $table->dropColumn('system');
            $table->dropColumn('opening_balance_date');
            $table->dropColumn('code')->nulable();
        });
    }
}
