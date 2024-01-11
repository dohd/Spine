<?php

namespace App\Models\client_vendor_ticket\Traits;

use App\Models\Access\User\User;
use App\Models\client_vendor_ticket\ClientVendorReply;

trait ClientVendorTicketRelationship
{
    public function replies()
    {
        return $this->hasMany(ClientVendorReply::class)->orderBy('index', 'ASC');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
