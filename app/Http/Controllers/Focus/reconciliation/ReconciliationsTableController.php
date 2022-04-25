<?php

namespace App\Http\Controllers\Focus\reconciliation;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\reconcilliation\ReconciliationRepository;

/**
 * Class BranchTableController.
 */
class ReconciliationsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ReconciliationRepository
     */
    protected $reconcilliation;

    /**
     * contructor to initialize repository object
     * @param ReconciliationRepository $productcategory ;
     */
    public function __construct(ReconciliationRepository $reconcilliation)
    {

        $this->reconcilliation = $reconcilliation;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->reconcilliation->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('account', function ($reconcilliation) {
                return $reconcilliation->account->holder;
            })
            ->addColumn('start_date', function ($reconcilliation) {
                return dateFormat($reconcilliation->start_date);
            })
            ->addColumn('end_date', function ($reconcilliation) {
                return dateFormat($reconcilliation->end_date);
            })
            ->addColumn('open_amount', function ($reconcilliation) {
                return number_format($reconcilliation->open_amount, 2);
            })
            ->addColumn('close_amount', function ($reconcilliation) {
                return number_format($reconcilliation->close_amount, 2);
            })
            ->addColumn('system_amount', function ($reconcilliation) {
                return number_format($reconcilliation->system_amount, 2);
            })
            ->addColumn('actions', function ($reconcilliation) {
                return;
            })
            ->make(true);
    }
}