<?php

namespace App\Models\projectstocktransfer\Traits;

/**
 * Class PurchaseorderAttribute.
 */
trait ProjectstocktransferAttribute
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
         '.$this->getViewButtonAttribute("purchaseorder-manage", "biller.projectstocktransfers.show").'
                '.$this->getEditButtonAttribute("purchaseorder-data", "biller.projectstocktransfers.edit").'
                '.$this->getDeleteButtonAttribute("purchaseorder-data", "biller.projectstocktransfers.destroy",'table').'
                ';
    }
}
