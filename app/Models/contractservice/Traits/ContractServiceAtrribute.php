<?php

namespace App\Models\contractservice\Traits;

/**
 * Class ProductcategoryAttribute.
 */
trait ContractServiceAtrribute
{
    // Make your attributes functions here
    // Further, see the documentation : https://laravel.com/docs/5.4/eloquent-mutators#defining-an-accessor


    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("project-manage", "biller.contractservices.show") 
        . ' ' . $this->getEditButtonAttribute("project-edit", "biller.contractservices.edit")
        . ' ' . $this->getDeleteButtonAttribute("project-delete", "biller.contractservices.destroy");
    }
}
