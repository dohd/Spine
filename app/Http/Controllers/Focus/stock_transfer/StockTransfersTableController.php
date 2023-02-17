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
namespace App\Http\Controllers\Focus\stock_transfer;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\stock_transfer\StockTransferRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class StockTransfersTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var StockTransferRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param StockTransferRepository $repository ;
     */
    public function __construct(StockTransferRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @param Request $request
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        $core = $this->repository->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()   
            ->addColumn('tid', function ($stock_transfer) {
                return gen4tid('TFR-', $stock_transfer->tid);
            })
            ->addColumn('source_location', function ($stock_transfer) {
                if ($stock_transfer->source_location)
                return $stock_transfer->source_location->title;
            })
            ->addColumn('destination_location', function ($stock_transfer) {
                if ($stock_transfer->destination_location)
                return $stock_transfer->destination_location->title;
            })
            ->addColumn('total', function ($stock_transfer) {
                return numberFormat($stock_transfer->total);
            })
            ->addColumn('actions', function ($stock_transfer) {
                return $stock_transfer->action_buttons;
            })
            ->make(true);
    }
}
