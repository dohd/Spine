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
        return $this->getViewButtonAttribute("manage-pricelist", "biller.pricelists.show")
        .' '. $this->getEditButtonAttribute("edit-pricelist", "biller.pricelists.edit")
        .' '.$this->getDeleteButtonAttribute("delete-pricelist", "biller.pricelists.destroy");     
    }
}
