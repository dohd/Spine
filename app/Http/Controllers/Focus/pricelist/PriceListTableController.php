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

namespace App\Http\Controllers\Focus\pricelist;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\pricelist\PriceListRepository;
use Yajra\DataTables\Facades\DataTables;

/**
 * Class BranchTableController.
 */
class PriceListTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var PriceListRepository
     */
    protected $pricelist;

    /**
     * contructor to initialize repository object
     * @param PriceListRepository $pricelist ;
     */
    public function __construct(PriceListRepository $pricelist)
    {

        $this->pricelist = $pricelist;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->pricelist->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('price', function ($pricelist) {
                return numberFormat($pricelist->price);
            })
            ->addColumn('actions', function ($pricelist) {
                return $pricelist->action_buttons;
            })
            ->make(true);
    }
}