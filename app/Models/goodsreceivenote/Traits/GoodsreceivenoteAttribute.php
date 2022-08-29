<?php

namespace App\Models\goodsreceivenote\Traits;

/**
 * Class ProductcategoryAttribute.
 */
trait GoodsreceivenoteAttribute
{
    // Make your attributes functions here
    // Further, see the documentation : https://laravel.com/docs/5.4/eloquent-mutators#defining-an-accessor


    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("project-manage", "biller.goodsreceivenote.show") . ' ' 
            . $this->getEditButtonAttribute("project-edit", "biller.goodsreceivenote.edit") . ' ' 
            . $this->getDeleteButtonAttribute("project-delete", "biller.goodsreceivenote.destroy");
    }
}