<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefaultAttrInBillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (['date', 'due_date', 'tax'] as $col) {
            $substr = $col == 'tax' ? 'INT DEFAULT 0' : 'DATE DEFAULT 0';
            DB::statement('ALTER TABLE rose_bills CHANGE '.$col.' '.$col.' '.$substr);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bills', function (Blueprint $table) {
            //
        });
    }
}
