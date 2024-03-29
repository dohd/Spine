<?php

namespace App\Models\odu\Traits;

/**
 * Class ProductcategoryAttribute.
 */
trait OduAttribute
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
         '.$this->getViewButtonAttribute("manage-customer", "biller.branches.show").'
                '.$this->getEditButtonAttribute("manage-customer", "biller.branches.edit").'
                '.$this->getDeleteButtonAttribute("customer-create", "biller.branches.destroy").'
                ';
    }
}
