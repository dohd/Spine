<?php

use Illuminate\Database\Migrations\Migration;

class RenameColumnsInBillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $names = [
            'transaction_ref' => 'transxn_ref',
            'stock_subtotal_amount' => 'stock_subttl',
            'stock_tax_amount' => 'stock_tax',
            'stock_grandtotal_amount' => 'stock_grandttl',
            'expense_subtotal_amount' => 'expense_subttl',
            'expense_tax_amount' => 'expense_tax',
            'expense_grandtotal_amount' => 'expense_grandttl',
            'asset_subtotal_amount' => 'asset_subttl',
            'asset_tax_amount' => 'asset_tax',
            'asset_grandtotal_amount' => 'asset_grandttl',
            'grand_tax_amount' => 'grandtax',
            'grand_total_amount' => 'grandttl',
            'total_amount_paid' => 'paidttl',
            'document_ref_type' => 'doc_ref_type',
            'document_ref' => 'doc_ref'
        ];
        foreach($names as $key => $val) {
            DB::statement('ALTER TABLE rose_bills RENAME COLUMN '. $key .' TO '. $val);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $names = [
            'transaction_ref' => 'transxn_ref',
            'stock_subtotal_amount' => 'stock_subttl',
            'stock_tax_amount' => 'stock_tax',
            'stock_grandtotal_amount' => 'stock_grandttl',
            'expense_subtotal_amount' => 'expense_subttl',
            'expense_tax_amount' => 'expense_tax',
            'expense_grandtotal_amount' => 'expense_grandttl',
            'asset_subtotal_amount' => 'asset_subttl',
            'asset_tax_amount' => 'asset_tax',
            'asset_grandtotal_amount' => 'asset_grandttl',
            'grand_tax_amount' => 'grandtax',
            'grand_total_amount' => 'grandttl',
            'total_amount_paid' => 'paidttl',
            'document_ref_type' => 'doc_ref_type',
            'document_ref' => 'doc_ref'
        ];
        foreach($names as $key => $val) {
            DB::statement('ALTER TABLE rose_bills RENAME COLUMN '. $val .' TO '. $key);
        }
    }
}
