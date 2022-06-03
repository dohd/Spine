<?php

namespace App\Models\task_schedule\Traits;


trait TaskScheduleAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("project-manage", "biller.taskschedules.show") 
        . ' ' . $this->getEditButtonAttribute("project-manage", "biller.taskschedules.edit")
        . ' ' . $this->getDeleteButtonAttribute("project-manage", "biller.taskschedules.destroy");
    }
}
