<?php

namespace App\Models\tenant_ticket\Traits;

trait TenantTicketAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-branch", "biller.tenant_tickets.show")
            . ' ' . $this->getEditButtonAttribute("edit-branch", "biller.tenant_tickets.edit")
            . ' ' . $this->getDeleteButtonAttribute("delete-branch", "biller.tenant_tickets.destroy");
    }
}