<?php

namespace App\Models\account\Traits;

/**
 * Class AccountAttribute.
 */
trait AccountAttribute
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
         '.$this->getViewButtonAttribute("view-account", "biller.accounts.show").'
                '.$this->getEditButtonAttribute("edit-account", "biller.accounts.edit").'
                '.$this->getDeleteButtonAttribute("delete-account", "biller.accounts.destroy").'
                ';
    }
}
