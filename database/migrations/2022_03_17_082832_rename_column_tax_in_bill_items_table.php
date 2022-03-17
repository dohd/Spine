<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameColumnTaxInBillItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $columns = ['tax_rate' => 'itemtax', 'tax' => 'taxrate'];
        foreach ($columns as $key => $val) {
            DB::statement('ALTER TABLE rose_bill_items RENAME COLUMN '. $key .' TO '.$val);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $columns = ['tax_rate' => 'itemtax', 'tax' => 'taxrate'];
        foreach ($columns as $key => $val) {
            DB::statement('ALTER TABLE rose_bill_items RENAME COLUMN '. $val .' TO '.$key);
        }
    }
}
