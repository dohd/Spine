<?php

namespace App\Models\leave\Traits;

trait LeaveAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("product-manage", "biller.leave.show")
        .' '. $this->getEditButtonAttribute("product-edit", "biller.leave.edit")
        .' '.$this->getDeleteButtonAttribute("product-delete", "biller.leave.destroy");     
    }
}
