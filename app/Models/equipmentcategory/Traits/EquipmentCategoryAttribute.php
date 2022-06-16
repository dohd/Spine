<?php

namespace App\Models\equipmentcategory\Traits;

/**
 * Class ProductcategoryAttribute.
 */
trait EquipmentCategoryAttribute
{
    // Make your attributes functions here
    // Further, see the documentation : https://laravel.com/docs/5.4/eloquent-mutators#defining-an-accessor


    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getEditButtonAttribute("task-edit", "biller.equipmentcategories.edit")
        //  . ' ' .$this->getViewButtonAttribute("task-manage", "biller.equipmentcategories.show")
         . ' ' .$this->getDeleteButtonAttribute("task-delete", "biller.equipmentcategories.destroy");
    }
}