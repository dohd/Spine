<?php

namespace App\Models\assetequipment\Traits;

/**
 * Class CustomerAttribute.
 */
trait AssetequipmentAttribute
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
         '.$this->getViewButtonAttribute("product-manage", "biller.assetequipments.show").'
                '.$this->getEditButtonAttribute("product-manage", "biller.assetequipments.edit").'
                '.$this->getDeleteButtonAttribute("product-manage", "biller.assetequipments.destroy",'table').'
                ';
    }
}
