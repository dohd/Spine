<?php

use Illuminate\Database\Migrations\Migration;

class RenameColumnSupplierInBillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       DB::statement('ALTER TABLE rose_bills RENAME COLUMN supplier TO suppliername');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE rose_bills RENAME COLUMN suppliername TO supplier');
    }
}
