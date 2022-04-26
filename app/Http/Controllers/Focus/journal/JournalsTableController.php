<?php
/*
 * Rose Business Suite - Accounting, CRM and POS Software
 * Copyright (c) UltimateKode.com. All Rights Reserved
 * ***********************************************************************
 *
 *  Email: support@ultimatekode.com
 *  Website: https://www.ultimatekode.com
 *
 *  ************************************************************************
 *  * This software is furnished under a license and may be used and copied
 *  * only  in  accordance  with  the  terms  of such  license and with the
 *  * inclusion of the above copyright notice.
 *  * If you Purchased from Codecanyon, Please read the full License from
 *  * here- http://codecanyon.net/licenses/standard/
 * ***********************************************************************
 */
namespace App\Http\Controllers\Focus\journal;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\journal\JournalRepository;
use Yajra\DataTables\Facades\DataTables;

class JournalsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var JournalRepository
     */
    protected $journal;

    /**
     * contructor to initialize repository object
     * @param JournalRepository $journal ;
     */
    public function __construct(JournalRepository $journal)
    {
        $this->journal = $journal;
    }

    /**
     * This method return the data of the model
     *
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->journal->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('date', function ($journal) {
                return dateFormat($journal->date);
            })
            ->addColumn('credit_ttl', function ($journal) {
                return number_format($journal->credit_ttl, 2);
            })
            ->addColumn('debit_ttl', function ($journal) {
                return number_format($journal->debit_ttl, 2);
            })
            ->addColumn('actions', function ($journal) {
                return $journal->action_buttons;
            })
            ->make(true);
    }
}