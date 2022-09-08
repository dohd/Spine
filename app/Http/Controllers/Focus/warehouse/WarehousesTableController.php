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

namespace App\Http\Controllers\Focus\warehouse;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\warehouse\WarehouseRepository;
use App\Http\Requests\Focus\warehouse\ManageWarehouseRequest;

/**
 * Class WarehousesTableController.
 */
class WarehousesTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var WarehouseRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param WarehouseRepository $repository ;
     */
    public function __construct(WarehouseRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @param ManageWarehouseRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageWarehouseRequest $request)
    {
        $core = $this->repository->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($warehouse) {
                return '<a class="font-weight-bold" href="' . route('biller.products.index') . '?rel_type=2&rel_id=' . $warehouse->id . '">' . $warehouse->title . '</a>';
            })
            ->addColumn('total', function ($warehouse) {
                return  $warehouse->products()->groupBy('parent_id')->where('qty', '>', 0)->get()->count();
            })
            ->addColumn('worth', function ($warehouse) {
                return numberFormat($warehouse->products()->where('qty', '>', 0)->get()->sum('purchase_price'));
            })
            ->addColumn('created_at', function ($warehouse) {
                return $warehouse->created_at->format('d-m-Y');
            })
            ->addColumn('actions', function ($warehouse) {
                return '<a class="btn btn-purple round" href="' . route('biller.products.index') . '?rel_type=2&rel_id=' . $warehouse->id . '" title="List"><i class="fa fa-list"></i></a>' . $warehouse->action_buttons;
            })
            ->make(true);
    }
}
