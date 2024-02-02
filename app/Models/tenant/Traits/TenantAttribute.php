<?php

namespace App\Models\tenant\Traits;

trait TenantAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-business-account", "biller.tenants.show")
            . ' ' . $this->getEditButtonAttribute("edit-business-account", "biller.tenants.edit")
            . ' ' . $this->getDeleteButtonAttribute("delete-business-account", "biller.tenants.destroy");
    }
}