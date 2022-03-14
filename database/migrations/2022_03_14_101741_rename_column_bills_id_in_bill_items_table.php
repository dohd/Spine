<?php

use Illuminate\Database\Migrations\Migration;

class RenameColumnBillsIdInBillItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE rose_bill_items RENAME COLUMN bills_id TO bill_id');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE rose_bill_items RENAME COLUMN bill_id TO bills_id');
    }
}
