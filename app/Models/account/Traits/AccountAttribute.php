<?php

namespace App\Models\account\Traits;

/**
 * Class AccountAttribute.
 */
trait AccountAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return '
         '.$this->getViewButtonAttribute("manage-account", "biller.accounts.show").'
                '.$this->getEditButtonAttribute("edit-account", "biller.accounts.edit").'
                '.$this->getDeleteButtonAttribute("delete-account", "biller.accounts.destroy").'
                ';
    }
}
