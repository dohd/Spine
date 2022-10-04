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
        return $this->getViewButtonAttribute("product-manage", "biller.client_products.show")
        .' '. $this->getEditButtonAttribute("product-edit", "biller.client_products.edit")
        .' '.$this->getDeleteButtonAttribute("product-delete", "biller.client_products.destroy");     
    }
}
