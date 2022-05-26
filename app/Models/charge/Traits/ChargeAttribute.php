<?php

namespace App\Models\charge\Traits;

/**
 * Class ProductcategoryAttribute.
 */
trait ChargeAttribute
{
    // Make your attributes functions here
    // Further, see the documentation : https://laravel.com/docs/5.4/eloquent-mutators#defining-an-accessor


    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("transaction-manage", "biller.charges.show")
            . ' ' . $this->getEditButtonAttribute("transaction-data", "biller.charges.edit")
            . ' ' . $this->getDeleteButtonAttribute("transaction-data", "biller.charges.destroy");
    }
}
