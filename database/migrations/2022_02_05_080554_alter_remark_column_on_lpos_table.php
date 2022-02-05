<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlterRemarkColumnOnLposTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lpos', function (Blueprint $table) {
            //
        });

        DB::statement('ALTER TABLE rose_lpos MODIFY remark VARCHAR(200) NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lpos', function (Blueprint $table) {
            //
        });
    }
}
