<?php

namespace App\Models\client_vendor_ticket\Traits;

trait ClientVendorTicketAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-branch", "biller.client_vendor_tickets.show")
            . ' ' . $this->getEditButtonAttribute("edit-branch", "biller.client_vendor_tickets.edit")
            . ' ' . $this->getDeleteButtonAttribute("delete-branch", "biller.client_vendor_tickets.destroy");
    }
}