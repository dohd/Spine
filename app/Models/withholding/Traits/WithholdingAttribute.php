<?php

namespace App\Models\withholding\Traits;

trait WithholdingAttribute
{
    // Make your attributes functions here
    // Further, see the documentation : https://laravel.com/docs/5.4/eloquent-mutators#defining-an-accessor


    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("project-manage", "biller.withholdings.show")
             .' '.$this->getEditButtonAttribute("project-edit", "biller.withholdings.edit")
             .' '.$this->getDeleteButtonAttribute("project-delete", "biller.withholdings.destroy");
    }
}
