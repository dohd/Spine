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
        return $this->getViewButtonAttribute("product-manage", "biller.opening_stock.show")
        .' '. $this->getEditButtonAttribute("product-edit", "biller.opening_stock.edit")
        .' '.$this->getDeleteButtonAttribute("product-delete", "biller.opening_stock.destroy");     
    }
}
