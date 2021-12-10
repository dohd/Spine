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

namespace App\Http\Controllers\Focus\djc;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\djc\DjcRepository;
use App\Http\Requests\Focus\djc\ManageDjcRequest;

/**
 * Class AccountsTableController.
 */
class DjcsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var AccountRepository
     */
    protected $djc;

    /**
     * contructor to initialize repository object
     * @param AccountRepository $account ;
     */
    public function __construct(DjcRepository $djc)
    {
        $this->djc = $djc;
    }

    /**
     * This method returns the datatable view
     */
    public function __invoke()
    {
        $core = $this->djc->getForDataTable();
                
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('tid', function($djc) {
                return 'Djc-' . sprintf('%04d', $djc->tid);
            })
            ->addColumn('customer', function ($djc) {
                if (isset($djc->client) && isset($djc->branch)) {
                    return $djc->client->company . ' - ' . $djc->branch->name 
                        .' <a class="font-weight-bold" href="' . route('biller.customers.show', [$djc->client->id]) . '"><i class="ft-eye"></i></a>';
                }
            })
            ->addColumn('created_at', function ($djc) {
                return dateFormat($djc->created_at);
            })
            ->addColumn('actions', function ($djc) {
                $valid_token = token_validator('', 'd' . $djc->id, true);
                $link = route('biller.print_djc', [$djc->id, 10, $valid_token, 1]);
                return '<a href="' . $link . '" target="_blank"  class="btn btn-purple round" data-toggle="tooltip" data-placement="top" title="Print"><i class="fa fa-print"></i></a> '
                    . $djc->action_buttons;
            })
            ->make(true);
    }
}
