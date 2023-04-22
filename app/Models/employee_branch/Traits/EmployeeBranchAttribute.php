<?php

namespace App\Models\employee_branch\Traits;

/**
 * Class employee_branchAttribute.
 */
trait EmployeeBranchAttribute
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
         '.$this->getViewButtonAttribute("manage-department", "biller.employee_branch.show").'
                '.$this->getEditButtonAttribute("edit-department", "biller.employee_branch.edit").'
                '.$this->getDeleteButtonAttribute("delete-department", "biller.employee_branch.destroy").'
                ';
    }
}
