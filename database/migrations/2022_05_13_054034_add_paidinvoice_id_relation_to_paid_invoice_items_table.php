<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaidinvoiceIdRelationToPaidInvoiceItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('paid_invoice_items', function (Blueprint $table) {
            $table->foreign('paidinvoice_id')->references('id')->on('paid_invoices')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('paid_invoice_items', function (Blueprint $table) {
            //
        });
    }
}
