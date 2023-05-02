<?php

namespace App\Models\client_product\Traits;

trait ClientProductAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-customer", "biller.client_products.show")
        .' '. $this->getEditButtonAttribute("edit-customer", "biller.client_products.edit")
        .' '.$this->getDeleteButtonAttribute("delete-customer", "biller.client_products.destroy");     
    }
}
