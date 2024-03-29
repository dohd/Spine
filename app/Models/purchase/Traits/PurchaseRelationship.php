<?php

namespace App\Models\purchase\Traits;

/**
 * Class PurchaseorderRelationship
 */
trait PurchaseRelationship

{
         public function customer()
    {
        return $this->belongsTo('App\Models\customer\Customer','payer_id','id')->withoutGlobalScopes();
    }
       public function supplier()
    {
        return $this->belongsTo('App\Models\supplier\Supplier','payer_id','id')->withoutGlobalScopes();
    }

 public function items_purchased()
    {
        return $this->hasMany('App\Models\purchase\Purchase','id','bill_id')->withoutGlobalScopes();
    }


       public function client()
    {
         return $this->hasOneThrough(Customer::class,Project::class,'customer_id','project_id','projects.ins as insi');
    }

     public function sum_expense()
    {
        return $this->hasMany('App\Models\purchase\Purchase','bill_id','id')->where('transaction_tab','2');
    }

    public function sum_tax()
    {
        return $this->hasMany('App\Models\purchase\Purchase','bill_id','id')->where('tax_type','sales_purchases');
    }

     /*public function client()
    {
         return $this->hasOneThrough(Customer::class,Project::class,'customer_id','project_id','projects.ins as insi');
    }*/


    public function branch()
    {
        return $this->belongsTo('App\Models\branch\Branch','branch_id','id')->withoutGlobalScopes();
    }

     public function ledger()
    {
        return $this->belongsTo('App\Models\account\Account','secondary_account_id','id')->withoutGlobalScopes();
    }


     public function project()
    {
        return $this->belongsTo('App\Models\project\Project','project_id','id')->withoutGlobalScopes();
    }




     public function products()
    {
        return $this->hasMany('App\Models\items\PurchaseItem','bill_id')->withoutGlobalScopes();
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
        return $this->hasMany('App\Models\transaction\Transaction','bill_id')->where('relation_id','=',9)->withoutGlobalScopes();
    }

    public function attachment()
    {
        return $this->hasMany('App\Models\items\MetaEntry','rel_id')->where('rel_type','=',9)->withoutGlobalScopes();
    }

}
