<?php

namespace App\Models\assetreturned\Traits;

/**
 * Class PurchaseorderAttribute.
 */
trait AssetreturnedAttribute
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
         '.$this->getViewButtonAttribute("manage-attendance", "biller.assetreturned.show").'
                '.$this->getEditButtonAttribute("edit-attendance", "biller.assetreturned.edit").'
                '.$this->getDeleteButtonAttribute("delete-attendance", "biller.assetreturned.destroy").'
                ';
    }
}
