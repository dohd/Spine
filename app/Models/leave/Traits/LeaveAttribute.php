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
        return $this->getViewButtonAttribute("manage-leave", "biller.leave.show")
        .' '. $this->getEditButtonAttribute("edit-leave", "biller.leave.edit")
        .' '.$this->getDeleteButtonAttribute("delete-leave", "biller.leave.destroy");     
    }
}
