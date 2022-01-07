<?php

namespace App\Http\Controllers\Focus\lpo;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class LpoTableController extends Controller
{
    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = LpoController::getForDataTable();
        return DataTables::of($core)
            ->addIndexColumn()
            ->addColumn('customer', function ($lpo) {
                return 'KCB - BIASHARA STREET';
            })
            ->addColumn('lpo_no', function ($lpo) {
                return $lpo->lpo_no;
            })
            ->addColumn('amount', function ($lpo) {
                return number_format($lpo->amount, 2);
            })
            ->addColumn('verified', function ($lpo) {
                return 'QT-001, QT-002';
            })
            ->addColumn('verified_uninvoiced', function ($lpo) {
                return 'QT-001, QT-002';
            })
            ->addColumn('approved_unverified', function ($lpo) {
                return 'QT-001, QT-002';
            })
            ->addColumn('balance', function ($lpo) {
                return 122000;
            })
            ->addColumn('actions', function ($lpo) {
                return '<a href="#" data-toggle="tooltip" data-placement="top" title="Edit"><i class="ft-edit fa-lg"></i></a>';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }
}
