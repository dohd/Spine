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
namespace App\Http\Controllers\Focus\opening_stock;

use App\Http\Controllers\Controller;
use Request;
use Yajra\DataTables\Facades\DataTables;


class OpeningStockTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var OpeningStockRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param OpeningStockRepository $repository ;
     */
    public function __construct(OpeningStockRepository $repository)
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
            ->addColumn('supplier', function ($opening_stock) {
                if ($opening_stock->supplier)
                return $opening_stock->supplier->name;
            })    
            ->addColumn('account', function ($opening_stock) {
                if ($opening_stock->account)
                return $opening_stock->account->holder;
            })
            ->addColumn('date', function ($opening_stock) {
                return dateFormat($opening_stock->date);
            })
            ->addColumn('amount', function ($opening_stock) {
                return numberFormat($opening_stock->amount);
            })
            ->addColumn('unallocated', function ($opening_stock) {
                return numberFormat($opening_stock->amount - $opening_stock->allocate_ttl);
            })
            ->addColumn('bill_no', function ($opening_stock) {
                return gen4tid('BILL-', $opening_stock->tid);
            })
            // ->addColumn('aggregate', function ($opening_stock) use($aggregate) {
            //     return $aggregate;
            // })
            ->addColumn('actions', function ($opening_stock) {
                return $opening_stock->action_buttons;
            })
            ->make(true);
    }
}
