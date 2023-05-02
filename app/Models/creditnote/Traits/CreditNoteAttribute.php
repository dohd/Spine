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
            ' '.$this->getEditButtonAttribute("edit-credit-note", "biller.creditnotes.edit")
            .' '.$this->getDeleteButtonAttribute("delete-credit-note", "biller.creditnotes.destroy");
    }
}
