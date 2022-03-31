<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToPaidinvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('paidinvoices', function (Blueprint $table) {
            $table->date('due_date');
            $table->string('payment_mode')->nullable();
            $table->string('doc_ref_type')->nullable();
            $table->string('doc_ref')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('paidinvoices', function (Blueprint $table) {
            //
        });
    }
}
