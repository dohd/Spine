<?php

namespace App\Models\invoice_payment\Traits;

/**
 * Class InvoiceAttribute.
 */
trait InvoicePaymentAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-invoice", "biller.invoice_payments.show")
            . ' ' . $this->getEditButtonAttribute("edit-invoice", "biller.invoice_payments.edit") 
            . ' ' . $this->getDeleteButtonAttribute("delete-invoice", "biller.invoice_payments.destroy");
    }
}
