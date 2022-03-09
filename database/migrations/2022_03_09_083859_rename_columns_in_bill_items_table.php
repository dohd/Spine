<?php

use Illuminate\Database\Migrations\Migration;

class RenameColumnsInBillItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $columns = [
            'item_project_id' => 'itemproject_id',
            'tax_amount' => 'tax',
            'item_type' => 'type'
        ];
        foreach ($columns as $key => $val) {
            DB::statement('ALTER TABLE rose_bill_items RENAME COLUMN '.$key.' TO '.$val);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $columns = [
            'item_project_id' => 'itemproject_id',
            'tax_amount' => 'tax',
            'item_type' => 'type'
        ];
        foreach ($columns as $key => $val) {
            DB::statement('ALTER TABLE rose_bill_items RENAME COLUMN '.$val.' TO '.$key);
        }
    }
}
