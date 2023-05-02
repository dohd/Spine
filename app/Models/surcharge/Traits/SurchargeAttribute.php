<?php

namespace App\Models\surcharge\Traits;

/**
 * Class PurchaseorderAttribute.
 */
trait SurchargeAttribute
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
         '.$this->getViewButtonAttribute("manage-attendance", "biller.surcharges.show").'
                '.$this->getEditButtonAttribute("edit-attendance", "biller.surcharges.edit").'
                '.$this->getDeleteButtonAttribute("delete-attendance", "biller.surcharges.destroy").'
                ';
    }
}
