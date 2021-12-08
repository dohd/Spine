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

namespace App\Http\Controllers\Focus\rjc;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\rjc\RjcRepository;
use App\Http\Requests\Focus\rjc\ManageRjcRequest;

/**
 * Class AccountsTableController.
 */
class RjcsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var AccountRepository
     */
    protected $rjc;

    /**
     * contructor to initialize repository object
     * @param AccountRepository $account ;
     */
    public function __construct(RjcRepository $rjc)
    {
        $this->rjc = $rjc;
    }

    /**
     * This method returns the datatable view
     */
    public function __invoke(ManageRjcRequest $request)
    {
        $core = $this->rjc->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('project_no', function ($rjc) {
                return $rjc->project->project_number;
            })
            ->addColumn('created_at', function ($rjc) {
                return dateFormat($rjc->created_at);
            })
            ->addColumn('actions', function ($rjc) {
                $valid_token = token_validator('', 'd' . $rjc->id, true);
                $link = route('biller.print_djc', [$rjc->id, 10, $valid_token, 1]);
                return '<a href="' . $link . '" target="_blank"  class="btn btn-purple round" data-toggle="tooltip" data-placement="top" title="Print"><i class="fa fa-print"></i></a> '
                    . $rjc->action_buttons;
            })
            ->make(true);
    }
}
