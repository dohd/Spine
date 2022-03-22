<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterColumnsInBudgetItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $cols = ['new_qty', 'price', 'product_qty'];
        foreach ($cols as $val) {
            DB::statement('ALTER TABLE rose_budget_items CHANGE '.$val.' '.$val.' DECIMAL(16,4) DEFAULT 0.0000');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('budget_items', function (Blueprint $table) {
            //
        });
    }
}
