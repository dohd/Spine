<?php

namespace App\Models\refill_customer\Traits;

trait RefillCustomerAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-product", "biller.refill_customers.show")
        .' '. $this->getEditButtonAttribute("edit-product", "biller.refill_customers.edit")
        .' '.$this->getDeleteButtonAttribute("delete-product", "biller.refill_customers.destroy");     
    }
}
