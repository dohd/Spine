<?php

namespace App\Models\banktransfer\Traits;

/**
 * Class ProductcategoryAttribute.
 */
trait BanktransferAttribute
{
    // Make your attributes functions here
    // Further, see the documentation : https://laravel.com/docs/5.4/eloquent-mutators#defining-an-accessor


    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return 
        //  $this->getViewButtonAttribute("transaction-manage", "biller.banktransfers.show") 
        $this->getEditButtonAttribute("manage-money-transfer", "biller.banktransfers.edit")
        . ' ' . $this->getDeleteButtonAttribute("manage-money-transfer", "biller.banktransfers.destroy");                
    }
}
