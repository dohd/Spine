<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameColumnDefaultAInAdditionalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('additionals', function (Blueprint $table) {
            //
        });
        DB::statement('ALTER TABLE rose_additionals RENAME COLUMN default_a TO is_default');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('additionals', function (Blueprint $table) {
            //
        });
        DB::statement('ALTER TABLE rose_additionals RENAME COLUMN is_default TO default_a');
    }
}
