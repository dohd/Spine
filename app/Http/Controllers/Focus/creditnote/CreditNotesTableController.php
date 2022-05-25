<?php
/* Copyright (c) UltimateKode.com. All Rights Reserved
 * ***********************************************************************
 *
 *  Email: support@ultimatekode.com
 *  Website: https://www.ultimatekode.com
 **
 * Rose Business Suite - Accounting, CRM and POS Software
 
 *  ************************************************************************
 *  * This software is furnished under a license and may be used and copied
 *  * only  in  accordance  with  the  terms  of such  license and with the
 *  * inclusion of the above copyright notice.
 *  * If you Purchased from Codecanyon, Please read the full License from
 *  * here- http://codecanyon.net/licenses/standard/
 * ***********************************************************************
 */

namespace App\Http\Controllers\Focus\creditnote;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\creditnote\CreditNoteRepository;
use Yajra\DataTables\Facades\DataTables;

class CreditNotesTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var CreditNoteRepository
     */
    protected $creditnote;

    /**
     * contructor to initialize bill object
     * @param CreditNoteRepository $creditnote ;
     */
    public function __construct(CreditNoteRepository $creditnote)
    {
        $this->creditnote = $creditnote;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->creditnote->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('tid', function ($creditnote) {
                $prefix = $creditnote->is_debit ? 'DN-': 'CN-';
                return gen4tid($prefix, $creditnote->tid);
            })
            ->addColumn('customer', function ($creditnote) {
                if ($creditnote->customer)
                    return $creditnote->customer->name;
            })
            ->addColumn('invoice_no', function ($creditnote) {
                if ($creditnote->invoice)
                    return '<a class="font-weight-bold" href="' . route('biller.invoices.show', $creditnote->invoice) . '">' 
                        . gen4tid('INV-', $creditnote->invoice->tid) . '</a>';
            })
            ->addColumn('amount', function ($creditnote) {
                return number_format($creditnote->total, 2);
            })
            ->addColumn('date', function ($creditnote) {
                return dateFormat($creditnote->date);
            })
            ->addColumn('actions', function ($creditnote) {
                return $creditnote->action_buttons;
            })
            ->make(true);
    }
}
