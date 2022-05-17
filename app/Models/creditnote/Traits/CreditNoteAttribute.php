<?php

namespace App\Models\creditnote\Traits;

/**
 * Class ProductcategoryAttribute.
 */
trait CreditNoteAttribute
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
            // $this->getViewButtonAttribute("transaction-manage", "biller.creditnotes.show")
            ' '.$this->getEditButtonAttribute("transaction-data", "biller.creditnotes.edit")
            .' '.$this->getDeleteButtonAttribute("transaction-data", "biller.creditnotes.destroy");
    }
}
