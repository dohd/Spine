<?php

namespace App\Models\utility_bill\Traits;

use App\Models\advance_payment\AdvancePayment;
use App\Models\goodsreceivenote\Goodsreceivenote;
use App\Models\items\BillpaymentItem;
use App\Models\items\TaxReportItem;
use App\Models\items\UtilityBillItem;
use App\Models\purchase\Purchase;
use App\Models\supplier\Supplier;

trait UtilityBillRelationship
{   
    public function payments()
    {
        return $this->hasMany(BillpaymentItem::class, 'bill_id');
    }

    public function purchase_tax_reports()
    {
        return $this->hasMany(TaxReportItem::class, 'purchase_id');
    }
    
    public function advance_payment()
    {
        return $this->belongsTo(AdvancePayment::class, 'ref_id');
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'ref_id');
    }

    public function grn()
    {
        return $this->belongsTo(Goodsreceivenote::class, 'ref_id');
    }

    public function items()
    {
        return $this->hasMany(UtilityBillItem::class, 'bill_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
