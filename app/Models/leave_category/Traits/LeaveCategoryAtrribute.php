<?php

namespace App\Models\leave_category\Traits;

trait LeaveCategoryAtrribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("product-manage", "biller.leave_category.show")
        .' '. $this->getEditButtonAttribute("product-edit", "biller.leave_category.edit")
        .' '.$this->getDeleteButtonAttribute("product-delete", "biller.leave_category.destroy");     
    }
}
