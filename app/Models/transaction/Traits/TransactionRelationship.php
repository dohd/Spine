<?php

namespace App\Models\transaction\Traits;

use App\Models\billpayment\Billpayment;
use App\Models\charge\Charge;
use App\Models\creditnote\CreditNote;
use App\Models\hrm\Hrm;
use App\Models\invoice_payment\InvoicePayment;
use App\Models\loan\Loan;
use App\Models\loan\Paidloan;
use App\Models\manualjournal\Journal;
use App\Models\purchase\Purchase;
use App\Models\utility_bill\UtilityBill;
use App\Models\withholding\Withholding;

/**
 * Class TransactionRelationship
 */
trait TransactionRelationship
{
    public function invoice_payment()
    {
        return $this->belongsTo(InvoicePayment::class, 'tr_ref');
    }

    public function bill_payment()
    {
        return $this->belongsTo(Billpayment::class, 'tr_ref');
    }

    public function journalentry() 
    {
        return $this->belongsTo(Journal::class, 'tr_ref');
    }

    public function debitnote()
    {
        return $this->belongsTo(CreditNote::class, 'tr_ref')->where('is_debit', 1);
    }

    public function creditnote()
    {
        return $this->belongsTo(CreditNote::class, 'tr_ref')->where('is_debit', 0);
    }

    public function withholding()
    {
        return $this->belongsTo(Withholding::class, 'tr_ref');
    }

    public function charge()
    {
        return $this->belongsTo(Charge::class, 'tr_ref');
    }

    public function paidloan()
    {
        return $this->belongsTo(Paidloan::class, 'tr_ref');
    }
    
    public function loan()
    {
        return $this->belongsTo(Loan::class, 'tr_ref');
    }

    public function paidinvoice()
    {
        return $this->belongsTo(InvoicePayment::class, 'tr_ref');
    }
    
    public function invoice()
    {
        return $this->belongsTo('App\Models\invoice\Invoice', 'tr_ref');
    }

    public function paidbill()
    {
        return $this->belongsTo(Billpayment::class, 'tr_ref');
    }

    public function bill()
    {
        return $this->hasOneThrough(Purchase::class, UtilityBill::class, 'ref_id', 'id', 'tr_ref', 'ref_id')->withoutGlobalScopes();
    }

    public function direct_purchase_bill()
    {
        return $this->belongsTo(UtilityBill::class, 'tr_ref', 'ref_id')->where('document_type', 'direct_purchase');
    }

    public function grn_invoice_bill()
    {
        return $this->belongsTo(UtilityBill::class, 'tr_ref')->where('document_type', 'goods_receive_note')->whereNotNull('ref_id');
    }

    public function grn_bill()
    {
        return $this->belongsTo(UtilityBill::class, 'tr_ref')->where('document_type', 'goods_receive_note')->whereNull('ref_id');
    }

    public function account()
    {
        return $this->belongsTo('App\Models\account\Account');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\customer\Customer', 'payer_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo('App\Models\supplier\Supplier', 'payer_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(Hrm::class, 'payer_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo('App\Models\transactioncategory\Transactioncategory', 'trans_category_id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\Access\User\User')->withoutGlobalScopes();
    }
}
