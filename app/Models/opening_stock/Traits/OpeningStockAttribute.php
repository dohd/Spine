<?php

namespace App\Models\opening_stock\Traits;

trait OpeningStockAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("product-manage", "biller.products.show")
        .' '. $this->getEditButtonAttribute("product-edit", "biller.products.edit")
        .' '.$this->getDeleteButtonAttribute("product-delete", "biller.products.destroy");     
    }
}
