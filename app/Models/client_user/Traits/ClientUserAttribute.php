<?php

namespace App\Models\client_user\Traits;

trait ClientUserAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-client", "biller.client_users.show")
        . ' ' . $this->getEditButtonAttribute("manage-client", "biller.client_users.edit")
        . ' ' . $this->getDeleteButtonAttribute("manage-client", "biller.client_users.destroy");
    }
}