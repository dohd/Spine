<?php

namespace App\Models\supplier\Traits;

/**
 * Class SupplierAttribute.
 */
trait SupplierAttribute
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
         '.$this->getViewButtonAttribute("manage-supplier", "biller.suppliers.show").'
                '.$this->getEditButtonAttribute("edit-supplier", "biller.suppliers.edit").'
                '.$this->getDeleteButtonAttribute("delete-supplier", "biller.suppliers.destroy").'
                ';
    }
}
