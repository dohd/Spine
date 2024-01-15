<?php

namespace App\Models\tenant_ticket\Traits;

use App\Models\Access\User\User;
use App\Models\tenant\Tenant;
use App\Models\tenant_ticket\TenantReply;

trait TenantTicketRelationship
{
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'ins');
    }

    public function replies()
    {
        return $this->hasMany(TenantReply::class)->orderBy('index', 'ASC');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
