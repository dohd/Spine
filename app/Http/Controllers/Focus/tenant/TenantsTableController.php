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

namespace App\Http\Controllers\Focus\tenant;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\tenant\TenantRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class TenantsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $tenant;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $productcategory ;
     */
    public function __construct(TenantRepository $tenant)
    {
        $this->tenant = $tenant;
    }

    /**
     * This method return the data of the model
     * @param ManageProductcategoryRequest $request
     *
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        $core = $this->tenant->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->editColumn('status', function ($tenant) {
                $variant = 'badge-secondary';
                if ($tenant->status == 'Active') $variant = 'badge-success';
                if ($tenant->status == 'Terminated') $variant = 'badge-danger';
                return '<span class="badge '. $variant .'">'. $tenant->status .'</span>';
            })
            ->addColumn('service', function ($tenant) {
                return @$tenant->package->service->name;
            })
            ->addColumn('pricing', function ($tenant) {
                return numberFormat(@$tenant->package->maintenance_cost);
            })
            ->addColumn('due_date', function ($tenant) {
                $due_date = @$tenant->package->due_date;
                if ($due_date) return date('d-M-Y', strtotime($due_date));
                return '';
            })
            ->addColumn('actions', function ($tenant) {
                return $tenant->action_buttons;
            })
            ->make(true);
    }
}
