<?php

namespace App\Models\issuance\Traits;

trait IssuanceAttribute
{
    public function getActionButtonsAttribute()
    {
        return ' '. $this->getViewButtonAttribute("project-manage", "biller.issuance.show") . ' ';
    }
}