<?php

namespace App\Http\Controllers\Focus\reconciliation;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\reconciliation\ReconciliationRepository;

class ReconciliationsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ReconciliationRepository
     */
    protected $reconciliation;

    /**
     * contructor to initialize repository object
     * @param ReconciliationRepository $reconciliation ;
     */
    public function __construct(ReconciliationRepository $reconciliation)
    {
        $this->reconciliation = $reconciliation;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->reconciliation->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('account', function ($reconciliation) {
                return $reconciliation->account->holder;
            })
            ->addColumn('start_date', function ($reconciliation) {
                return dateFormat($reconciliation->start_date);
            })
            ->addColumn('end_date', function ($reconciliation) {
                return dateFormat($reconciliation->end_date);
            })
            ->addColumn('open_amount', function ($reconciliation) {
                return number_format($reconciliation->open_amount, 2);
            })
            ->addColumn('close_amount', function ($reconciliation) {
                return number_format($reconciliation->close_amount, 2);
            })
            ->addColumn('system_amount', function ($reconciliation) {
                return number_format($reconciliation->system_amount, 2);
            })
            ->addColumn('actions', function ($reconciliation) {
                return $reconciliation->action_buttons;
            })
            ->make(true);
    }
}