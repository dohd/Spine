<?php

namespace App\Models\tenant_ticket\Traits;

use App\Models\Access\User\User;
use App\Models\tenant_ticket\TenantTicket;

trait TenantReplyRelationship
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tenant_ticket()
    {
        return $this->belongsTo(TenantTicket::class, 'tenant_ticket_id');
    }
}
