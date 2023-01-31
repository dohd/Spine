<?php

namespace App\Models\invoice\Traits;

use App\Models\creditnote\CreditNote;
use App\Models\currency\Currency;
use App\Models\project\ProjectRelations;
use App\Models\lead\Lead;
use App\Models\customer\Customer;
use App\Models\items\InvoiceItem;
use App\Models\items\PaidInvoiceItem;
use App\Models\items\TaxReportItem;
use App\Models\items\WithholdingItem;
use App\Models\quote\Quote;

/**
 * Class InvoiceRelationship
 */
trait InvoiceRelationship
{
    public function quotes()
    {
        return $this->hasManyThrough(Quote::class, InvoiceItem::class, 'invoice_id', 'id', 'id', 'quote_id');
    }

    public function invoice_tax_reports()
    {
        return $this->hasMany(TaxReportItem::class);
    }

    public function withholding_payments()
    {
        return $this->hasMany(WithholdingItem::class);
    }

    public function payments()
    {
        return $this->hasMany(PaidInvoiceItem::class);
    }

    public function creditnotes()
    {
        return $this->hasMany(CreditNote::class)->whereNull('supplier_id');
    }

    public function debitnotes()
    {
        return $this->hasMany(CreditNote::class)->whereNull('customer_id');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\customer\Customer')->withoutGlobalScopes();
    }

    public function products()
    {
        return $this->hasMany('App\Models\items\InvoiceItem')->withoutGlobalScopes();
    }

    public function user()
    {
        return $this->belongsTo('App\Models\Access\User\User')->withoutGlobalScopes();
    }
    public function term()
    {
        return $this->belongsTo('App\Models\term\Term')->withoutGlobalScopes();
    }

    public function transactions()
    {
        return $this->hasMany('App\Models\transaction\Transaction', 'tr_ref')->whereIn('tr_type', ['inv', 'endprj']);
    }

    public function attachment()
    {
        return $this->hasMany('App\Models\items\MetaEntry', 'rel_id')->where('rel_type', '=', 1)->withoutGlobalScopes();
    }

    public function project()
    {
        return $this->belongsTo(ProjectRelations::class, 'id',  'rid')->where('related', '=', 7);
    }

    public function client()
    {
        return $this->hasOneThrough(Customer::class, Lead::class, 'id', 'id', 'lead_id', 'client_id')->withoutGlobalScopes();
    }

    public function branch()
    {
        return $this->hasOneThrough(Branch::class, Lead::class, 'id', 'id', 'lead_id', 'branch_id')->withoutGlobalScopes();
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
