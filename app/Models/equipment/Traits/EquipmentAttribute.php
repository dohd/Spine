<?php

namespace App\Models\equipment\Traits;

/**
 * Class OrderAttribute.
 */
trait EquipmentAttribute
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
         '.$this->getViewButtonAttribute("project-manage", "biller.equipments.show").'
                '.$this->getEditButtonAttribute("project-manage", "biller.equipments.edit").'
                '.$this->getDeleteButtonAttribute("project-manage", "biller.equipments.destroy").'
                ';


        
    }
}
