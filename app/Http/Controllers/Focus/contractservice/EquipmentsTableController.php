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

namespace App\Http\Controllers\Focus\contractservice;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\contractservice\ContractServiceRepository;
use Yajra\DataTables\Facades\DataTables;

/**
 * Class BranchTableController.
 */
class EquipmentsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ContractServiceRepository
     */
    protected $contractservice;

    /**
     * contructor to initialize repository object
     * @param ContractServiceRepository $contractservice;
     */
    public function __construct(ContractServiceRepository $contractservice)
    {
        $this->contractservice = $contractservice;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->contractservice->getServiceReportItemsForDataTable();
        
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('jobcard_no', function ($item) {
                $service = $item->contractservice;
                if ($service)
                return $service->jobcard_no;
            })
            ->addColumn('tid', function ($item) {
                if ($item->equipment)
                return '<a href="'. route('biller.equipments.edit', $item->equipment) .'">'. gen4tid('Eq-', $item->equipment->tid) .'</a>';
            })
            ->addColumn('descr', function ($item) {
                if ($item->equipment)
                return "{$item->equipment->make_type} {$item->equipment->capacity}";
            })
            ->addColumn('location', function ($item) {
                if ($item->equipment)
                return $item->equipment->location;
            })
            ->addColumn('rate', function ($item) {
                if ($item->equipment)
                return numberFormat($item->equipment->service_rate);
            })
            ->addColumn('is_bill', function ($item) {
                if (!$item->is_bill) return 'No';
                return 'Yes';
            })
            ->make(true);
    }
}
