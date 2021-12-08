<?php

namespace App\Models\rjc\Traits;

/**
 * Class ProductcategoryAttribute.
 */
trait RjcAttribute
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
         '.$this->getViewButtonAttribute("project-manage", "biller.rjcs.show").'
                '.$this->getEditButtonAttribute("project-edit", "biller.rjcs.edit").'
                '.$this->getDeleteButtonAttribute("project-delete", "biller.rjcs.destroy").'
                ';
    }
}
