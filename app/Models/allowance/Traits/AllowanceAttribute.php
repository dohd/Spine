<?php

namespace App\Models\allowance\Traits;

/**
 * Class AllowanceAttribute.
 */
trait AllowanceAttribute
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
         '.$this->getViewButtonAttribute("department-manage", "biller.allowances.show").'
                '.$this->getEditButtonAttribute("department-data", "biller.allowances.edit").'
                '.$this->getDeleteButtonAttribute("department-data", "biller.allowances.destroy").'
                ';
    }
}
