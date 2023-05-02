<?php

namespace App\Models\assetissuance\Traits;

/**
 * Class PurchaseorderAttribute.
 */
trait AssetissuanceAttribute
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
         '.$this->getViewButtonAttribute("manage-attendance", "biller.assetissuance.show").'
                '.$this->getEditButtonAttribute("edit-attendance", "biller.assetissuance.edit").'
                '.$this->getDeleteButtonAttribute("delete-attendance", "biller.assetissuance.destroy").'
                ';
    }
}
