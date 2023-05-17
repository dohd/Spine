<?php

namespace App\Models\overtimepay\Traits;

/**
 * Class DepartmentAttribute.
 */
trait OvertimePayAttribute
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
         '.$this->getViewButtonAttribute("manage-holiday", "biller.overtimepay.show").'
                '.$this->getEditButtonAttribute("edit-holiday", "biller.overtimepay.edit").'
                '.$this->getDeleteButtonAttribute("delete-holiday", "biller.overtimepay.destroy").'
                ';
    }
}
