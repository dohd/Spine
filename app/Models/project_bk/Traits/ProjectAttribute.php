<?php

namespace App\Models\project\Traits;

/**
 * Class ProjectAttribute.
 */
trait ProjectAttribute
{
    // Make your attributes functions here
    // Further, see the documentation : https://laravel.com/docs/5.4/eloquent-mutators#defining-an-accessor


    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-project", "biller.projects.show")
         . ' ' . $this->getEditButtonAttribute("edit-project", "biller.projects.edit")
         . ' ' . $this->getDeleteButtonAttribute("delete-project", "biller.projects.destroy");
    }
}
