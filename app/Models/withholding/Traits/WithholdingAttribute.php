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
        return $this->getViewButtonAttribute("manage-withholding-cert", "biller.withholdings.show")
             .' '.$this->getEditButtonAttribute("edit-withholding-cert*", "biller.withholdings.edit")
             .' '.$this->getDeleteButtonAttribute("delete-withholding-cert", "biller.withholdings.destroy");
    }
}
