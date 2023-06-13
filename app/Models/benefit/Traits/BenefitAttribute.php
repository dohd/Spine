<?php

namespace App\Models\benefit\Traits;

/**
 * Class DepartmentAttribute.
 */
trait BenefitAttribute
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
         '.$this->getViewButtonAttribute("manage-holiday", "biller.benefits.show").'
                '.$this->getEditButtonAttribute("edit-holiday", "biller.benefits.edit").'
                '.$this->getDeleteButtonAttribute("delete-holiday", "biller.benefits.destroy").'
                ';
    }
}
