<?php

namespace App\Models\product_refill\Traits;

trait ProductRefillAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-product", "biller.product_refills.show")
        .' '. $this->getEditButtonAttribute("edit-product", "biller.product_refills.edit")
        .' '.$this->getDeleteButtonAttribute("delete-product", "biller.product_refills.destroy");     
    }
}
