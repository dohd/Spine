<?php

namespace App\Models\client_vendor_ticket\Traits;

use App\Models\Access\User\User;
use App\Models\client_vendor_ticket\ClientVendorReply;
use App\Models\customer\Customer;
use App\Models\ticket_category\TicketCategory;

trait ClientVendorTicketRelationship
{
    public function category()
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function replies()
    {
        return $this->hasMany(ClientVendorReply::class)->orderBy('index', 'ASC');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
