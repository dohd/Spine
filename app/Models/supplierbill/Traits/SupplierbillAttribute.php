<?php

namespace App\Models\supplierbill\Traits;


trait SupplierbillAttribute
{
    // Make your attributes functions here
    // Further, see the documentation : https://laravel.com/docs/5.4/eloquent-mutators#defining-an-accessor


    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("project-manage", "biller.supplierbills.show") . ' ' 
            . $this->getEditButtonAttribute("project-edit", "biller.supplierbills.edit") . ' ' 
            . $this->getDeleteButtonAttribute("project-delete", "biller.supplierbills.destroy");
    }
}
