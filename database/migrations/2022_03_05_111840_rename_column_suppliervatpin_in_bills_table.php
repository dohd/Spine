<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameColumnSuppliervatpinInBillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bills', function (Blueprint $table) {
            //
        });
        DB::statement('ALTER TABLE rose_bills RENAME COLUMN supplier_vat_pin TO supplier_taxid');
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
        DB::statement('ALTER TABLE rose_bills RENAME COLUMN supplier_taxid TO supplier_vat_pin');

    }
}
