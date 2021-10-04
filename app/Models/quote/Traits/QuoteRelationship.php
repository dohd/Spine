<?php

namespace App\Models\quote\Traits;
use App\Models\customer\Customer;
use App\Models\branch\Branch;
use App\Models\lead\Lead;
/**
 * Class QuoteRelationship
 */
trait QuoteRelationship
{
       public function customer()
    {
        return $this->belongsTo('App\Models\customer\Customer')->withoutGlobalScopes();
    }
     public function customer_branch()
    {
        return $this->belongsTo('App\Models\branch\Branch')->withoutGlobalScopes();
    }

     public function products()
    {
        return $this->hasMany('App\Models\items\QuoteItem')->withoutGlobalScopes();
    }

    public function user()
    {
        return $this->belongsTo('App\Models\Access\User\User')->withoutGlobalScopes();
    }
    public function term()
    {
        return $this->belongsTo('App\Models\term\Term')->withoutGlobalScopes();
    }


    public function attachment()
    {
        return $this->hasMany('App\Models\items\MetaEntry','rel_id')->where('rel_type','=',4)->withoutGlobalScopes();
    }



      public function client()
    {
         return $this->hasOneThrough(Customer::class,Lead::class,'id','id','lead_id','client_id')->withoutGlobalScopes();
    }


     public function branch()
    {
         return $this->hasOneThrough(Branch::class,Lead::class,'id','id','lead_id','branch_id')->withoutGlobalScopes();
    }


     public function lead()
    {
         return $this->hasOne(Lead::class,'id','lead_id')->withoutGlobalScopes();
    }



}
