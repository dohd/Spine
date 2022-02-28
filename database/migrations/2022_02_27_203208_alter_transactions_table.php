<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('is_bill');
            $table->dropColumn('rate');
            $table->dropColumn('total_amount');
            $table->dropColumn('total_paid_amount');
            $table->dropColumn('payer_type');
            $table->dropColumn('payer');
            $table->dropColumn('ref_type');
            $table->dropColumn('refer_no');
            $table->dropColumn('payer_id');
            $table->dropColumn('method');
            $table->dropColumn('for_who');
            $table->dropColumn('payment_date');
            $table->dropColumn('bill_id');
            $table->dropColumn('item_name');
            $table->dropColumn('taxid');
            $table->dropColumn('relation_id');
            $table->dropColumn('item_id');
            $table->dropColumn('qty');
            $table->dropColumn('tax');
            $table->dropColumn('tax_amount');
            $table->dropColumn('taxable_amount');
            $table->dropColumn('discount_rate');
            $table->dropColumn('discount');
            $table->dropColumn('discountformat');
            $table->dropColumn('unit');
            $table->dropColumn('secondary_account_id');
            $table->dropColumn('project_id');
            $table->dropColumn('branch_id');
            $table->dropColumn('transaction_tab');
            $table->dropColumn('grn');
            $table->dropColumn('taxformat');
            $table->dropColumn('s_warehouses');
            $table->dropColumn('invoice_id');
            $table->dropColumn('requested_by');
            $table->dropColumn('approved_by');
            $table->dropColumn('s_warehouses');
            $table->string('tr_ref')->nullable();
            $table->integer('user_type')->default(0);
            $table->integer('tr_user_id')->default(0);
            $table->integer('is_primary')->default(0);
            
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('tr_ref');
            $table->dropColumn('user_type');
            $table->dropColumn('tr_user_id');
            $table->dropColumn('is_primary');
        });
    }
}
