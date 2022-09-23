<?php

namespace App\Models\attendance\Traits;

trait AttendanceAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("product-manage", "biller.attendances.show")
        // .' '. $this->getEditButtonAttribute("product-edit", "biller.attendances.edit")
        .' '.$this->getDeleteButtonAttribute("product-delete", "biller.attendances.destroy");     
    }
}
