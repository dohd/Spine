<?php

namespace App\Models\refill_product\Traits;

trait RefillProductAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-product", "biller.refill_products.show")
        .' '. $this->getEditButtonAttribute("edit-product", "biller.refill_products.edit")
        .' '.$this->getDeleteButtonAttribute("delete-product", "biller.refill_products.destroy");     
    }
}
