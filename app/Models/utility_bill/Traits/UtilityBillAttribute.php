<?php

namespace App\Models\utility_bill\Traits;


trait UtilityBillAttribute
{
    // Make your attributes functions here
    // Further, see the documentation : https://laravel.com/docs/5.4/eloquent-mutators#defining-an-accessor


    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-bill", "biller.utility-bills.show") . ' ' 
            . $this->getEditButtonAttribute("edit-bill", "biller.utility-bills.edit") . ' ' 
            . $this->getDeleteButtonAttribute("delete-bill", "biller.utility-bills.destroy");
    }
}
