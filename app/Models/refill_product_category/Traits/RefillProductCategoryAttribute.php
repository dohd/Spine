<?php

namespace App\Models\refill_product_category\Traits;

trait RefillProductCategoryAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-product", "biller.refill_product_categories.show")
        .' '. $this->getEditButtonAttribute("edit-product", "biller.refill_product_categories.edit")
        .' '.$this->getDeleteButtonAttribute("delete-product", "biller.refill_product_categories.destroy");     
    }
}
