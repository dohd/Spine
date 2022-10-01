<?php

namespace App\Models\leave\Traits;

trait AdvancePaymentAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("product-manage", "biller.advance_payments.show")
        .' '. $this->getEditButtonAttribute("product-edit", "biller.advance_payments.edit")
        .' '.$this->getDeleteButtonAttribute("product-delete", "biller.advance_payments.destroy");     
    }
}
