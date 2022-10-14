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
        return $this->getViewButtonAttribute("product-manage", "biller.pricelists.show")
        .' '. $this->getEditButtonAttribute("product-edit", "biller.pricelists.edit")
        .' '.$this->getDeleteButtonAttribute("product-delete", "biller.pricelists.destroy");     
    }
}
