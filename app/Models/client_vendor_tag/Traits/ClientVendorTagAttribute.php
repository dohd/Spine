<?php

namespace App\Models\client_vendor_tag\Traits;

trait ClientVendorTagAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getEditButtonAttribute("manage-client", "biller.client_vendor_tags.edit")
            . ' ' . $this->getDeleteButtonAttribute("manage-client", "biller.client_vendor_tags.destroy");
    }
}