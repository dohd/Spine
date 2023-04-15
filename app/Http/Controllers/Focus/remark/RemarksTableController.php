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

namespace App\Http\Controllers\Focus\remark;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\remark\RemarkRepository;

/**
 * Class BranchTableController.
 */
class RemarksTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $remark;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $productcategory ;
     */
    public function __construct(RemarkRepository $remark)
    {

        $this->remark = $remark;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->remark->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('created_at', function ($remark) {
                $created = $remark->created_at;

                return $created;
            })
            ->addColumn('recepient', function ($remark) {
                $recepient = $remark->recepient;

                return $recepient;
            })
            ->addColumn('remarks', function ($remark) {
                $remark = $remark->remarks;

                return $remark;
            })
            ->addColumn('reminder_date', function ($remark) {
                $date = $remark->reminder_date;

                return $date;
            })
            ->addColumn('actions', function ($remark) {
                return $remark->action_buttons;
            })
            ->make(true);
    }
}
