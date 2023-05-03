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

namespace App\Http\Controllers\Focus\calllist;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\calllist\CallListRepository;

/**
 * Class BranchTableController.
 */
class CallListTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $calllist;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $productcategory ;
     */
    public function __construct(CallListRepository $calllist)
    {

        $this->calllist = $calllist;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->calllist->getForDataTable();
       
        return Datatables::of($core)
        ->escapeColumns(['id'])
        ->addIndexColumn()
        ->addColumn('title', function ($calllist) {

            $title = $calllist->title == null ? '_____________' : $calllist->title;
            return $title;
        })
        ->addColumn('category', function ($calllist) {
            $category = $calllist->category == null ? '_____________' : $calllist->category ;

            return $category;
        })
        ->addColumn('prospects_number', function ($calllist) {
            $prospects_number = $calllist->prospects_number == null ? '_____________' : $calllist->prospects_number;

            return $prospects_number;
        })
        ->addColumn('start_date', function ($calllist) {
            $start_date = $calllist->start_date == null ? '_____________': $calllist->start_date;

            return $start_date;
        })
        ->addColumn('end_date', function ($calllist) {
            $end_date = $calllist->end_date == null ? '_____________' : $calllist->end_date;

            return $end_date;
        })
        ->addColumn('actions', function ($calllist) {
            return $calllist->action_buttons;
        })
        ->make(true);
    }

}
