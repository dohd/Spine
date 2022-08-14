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
namespace App\Http\Controllers\Focus\projectstock;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\projectstock\ProjectStockRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class ProjectStockTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProjectStockRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ProjectStockRepository $repository ;
     */
    public function __construct(ProjectStockRepository $repository)
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
            ->addColumn('tid', function ($projectstock) {
                return gen4tid('GRN-', $projectstock->tid);
            })
            ->addColumn('supplier', function ($projectstock) {
                if ($projectstock->supplier)
                return $projectstock->supplier->name;
            })        
            ->addColumn('purchase_type', function ($projectstock) {
                $purchaseorder = $projectstock->purchaseorder;
                if ($purchaseorder) {
                    $lpo_no = gen4tid('PO-', $purchaseorder->tid);
                    $note = $purchaseorder->note;

                    return $lpo_no . ' - ' . $note;
                }
            })
            ->addColumn('dnote', function ($projectstock) {
                return $projectstock->dnote;
            })
            ->addColumn('date', function ($projectstock) {
                return dateFormat($projectstock->date);
            })
            ->addColumn('actions', function ($projectstock) {
                return $projectstock->action_buttons;
            })
            ->make(true);
    }
}
