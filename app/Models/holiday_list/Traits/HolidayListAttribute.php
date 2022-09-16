<?php

namespace App\Models\holiday_list\Traits;

trait HolidayListAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("product-manage", "biller.holiday_list.show")
        .' '. $this->getEditButtonAttribute("product-edit", "biller.holiday_list.edit")
        .' '.$this->getDeleteButtonAttribute("product-delete", "biller.holiday_list.destroy");     
    }
}
