<?php

namespace App\Models\overtimerate\Traits;

/**
 * Class DepartmentAttribute.
 */
trait OvertimeRateAttribute
{
    // Make your attributes functions here
    // Further, see the documentation : https://laravel.com/docs/5.4/eloquent-mutators#defining-an-accessor


    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return '
         '.$this->getViewButtonAttribute("manage-holiday", "biller.overtimerates.show").'
                '.$this->getEditButtonAttribute("edit-holiday", "biller.overtimerates.edit").'
                '.$this->getDeleteButtonAttribute("delete-holiday", "biller.overtimerates.destroy").'
                ';
    }
}
