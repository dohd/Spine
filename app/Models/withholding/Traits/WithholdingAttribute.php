<?php

namespace App\Models\withholding\Traits;

/**
 * Class ProductcategoryAttribute.
 */
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
        return $this->getViewButtonAttribute("transaction-manage", "biller.withholdings.show");
        //  .' '.$this->getEditButtonAttribute("transaction-data", "biller.charges.edit")
        //  .' '.$this->getDeleteButtonAttribute("transaction-data", "biller.charges.destroy");
    }
}
