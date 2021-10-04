<?php

namespace App\Models\lead\Traits;

/**
 * Class ProductcategoryAttribute.
 */
trait LeadAttribute
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
         '.$this->getViewButtonAttribute("project-manage", "biller.leads.show").'
                '.$this->getEditButtonAttribute("project-edit", "biller.leads.edit").'
                '.$this->getDeleteButtonAttribute("project-delete", "biller.leads.destroy").'
                ';
    }
}
