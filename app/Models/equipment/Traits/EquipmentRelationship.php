<?php

namespace App\Models\equipment\Traits;

/**
 * Class EquipmentRelationship
 */
trait EquipmentRelationship
{
    public function customer()
    {
        return $this->belongsTo('App\Models\customer\Customer');
    }

    public function region()
    {
        return $this->hasOne('App\Models\region\Region', 'id', 'region_id')->withoutGlobalScopes();
    }
    public function project_section()
    {
        return $this->hasOne('App\Models\section\Section', 'id', 'section_id')->withoutGlobalScopes();
    }



    public function category()
    {
        return $this->hasOne('App\Models\equipmentcategory\EquipmentCategory', 'id', 'equipment_category_id')->withoutGlobalScopes();
    }

    public function branch()
    {
        return $this->belongsTo('App\Models\branch\Branch');
    }

    public function indoor()
    {
        return $this->hasOne('App\Models\equipment\Equipment', 'id', 'rel_id')->withoutGlobalScopes();
    }

    public function supplier()
    {
        return $this->hasOne('App\Models\supplier\Supplier', 'id', 'customer_id')->withoutGlobalScopes();
    }

    public function products()
    {
        return $this->hasMany('App\Models\items\OrderItem')->withoutGlobalScopes();
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
        return $this->hasMany('App\Models\transaction\Transaction', 'bill_id')->where('relation_id', '=', 5)->withoutGlobalScopes();
    }

    public function attachment()
    {
        return $this->hasMany('App\Models\items\MetaEntry', 'rel_id')->where('rel_type', '=', 5)->withoutGlobalScopes();
    }
}
