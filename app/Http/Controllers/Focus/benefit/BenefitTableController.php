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
namespace App\Http\Controllers\Focus\benefit;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\benefit\BenefitRepository;
//use App\Http\Requests\Focus\benefit\ManagebenefitRequest;

/**
 * Class benefitsTableController.
 */
class BenefitTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var benefitRepository
     */
    protected $benefit;

    /**
     * contructor to initialize repository object
     * @param benefitRepository $benefit ;
     */
    public function __construct(BenefitRepository $benefit)
    {
        $this->benefit = $benefit;
    }

    /**
     * This method return the data of the model
     * @param ManagebenefitRequest $request
     *
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        //
        $core = $this->benefit->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($benefit) {
                  return $benefit->name;
                // return '<a href="' . route('biller.ji.index') . '?rel_type=2&rel_id=' . $benefit->id . '">' . $benefit->name . '</a>';
            })
            ->addColumn('amount', function ($benefit) {
                // return $benefit->users->count('*');
                return $benefit->amount;
            })
            ->addColumn('created_at', function ($benefit) {
                return Carbon::parse($benefit->created_at)->toDateString();
            })
            ->addColumn('actions', function ($benefit) {
                // return '<a href="' . route('biller.hrms.index') . '?rel_type=2&rel_id=' . $benefit->id . '" class="btn btn-purple round" data-toggle="tooltip" data-placement="top" title="List"><i class="fa fa-list"></i></a> ' . $benefit->action_buttons;
                return $benefit->action_buttons;
            })
            ->make(true);
    }
}
