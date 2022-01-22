<?php

namespace App\Models\quote\Traits;

use App\Models\Access\User\User;
use App\Models\customer\Customer;
use App\Models\branch\Branch;
use App\Models\items\MetaEntry;
use App\Models\items\QuoteItem;
use App\Models\lead\Lead;
use App\Models\lpo\Lpo;
use App\Models\project\ProjectQuote;
use App\Models\term\Term;
use App\Models\verifiedjcs\VerifiedJc;

/**
 * Class QuoteRelationship
 */
trait QuoteRelationship
{
    public function lpo()
    {
        return $this->belongsTo(Lpo::class);
    }

    public function project_quote()
    {
        return $this->hasOne(ProjectQuote::class);
    }

    public function verified_jcs()
    {
        return $this->hasMany(VerifiedJc::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function products()
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withoutGlobalScopes();
    }

    public function term()
    {
        return $this->belongsTo(Term::class)->withoutGlobalScopes();
    }

    public function attachment()
    {
        return $this->hasMany(MetaEntry::class, 'rel_id')->where('rel_type', '=', 4)->withoutGlobalScopes();
    }

    public function client()
    {
        return $this->hasOneThrough(Customer::class, Lead::class, 'id', 'id', 'lead_id', 'client_id')->withoutGlobalScopes();
    }

    public function branch()
    {
        return $this->hasOneThrough(Branch::class, Lead::class, 'id', 'id', 'lead_id', 'branch_id')->withoutGlobalScopes();
    }

    public function lead()
    {
        return $this->hasOne(Lead::class, 'id', 'lead_id')->withoutGlobalScopes();
    }
}
