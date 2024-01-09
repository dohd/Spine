<?php

namespace App\Models\client_vendor\Traits;

trait ClientVendorAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-branch", "biller.client_vendors.show")
            . ' ' . $this->getEditButtonAttribute("edit-branch", "biller.client_vendors.edit")
            . ' ' . $this->getDeleteButtonAttribute("delete-branch", "biller.client_vendors.destroy");
    }
}