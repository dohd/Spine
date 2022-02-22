<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToAccountTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_types', function (Blueprint $table) {
            $table->dropForeign('rose_account_types_fk1');
            $table->dropColumn(['account_id']);
            $table->dropColumn(['account_name']);

            $table->string('name')->nullable();
            $table->string('category')->nullable();
            $table->string('description')->nulable();
            $table->string('system')->nulable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('account_types', function (Blueprint $table) {
            //
        });
    }
}
