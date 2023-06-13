<?php

namespace App\Http\Controllers\Focus\surcharge;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\surcharge\SurchargeRepository;

class SurchargeTableController extends Controller
{
     /**
     * variable to store the repository object
     * @var surchargeRepository
     */
    protected $surcharge;

    /**
     * contructor to initialize repository object
     * @param surchargeRepository $assetorder ;
     */
    public function __construct(SurchargeRepository $surcharge)
    {
        $this->surcharge = $surcharge;
    }

    /**
     * This method return the data of the model
     * @param Request $request
     *
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        $core = $this->surcharge->getForDataTable();

        return Datatables::of($core)
            ->addIndexColumn()
            ->escapeColumns(['id'])
            ->addColumn('employee_id', function ($surcharge) {
                return $surcharge->employee_id;
            })
            ->addColumn('employee_name', function ($surcharge) {
                $name = $surcharge->employee_name;
                return $name;
            })
            ->addColumn('issue_type', function ($surcharge) {
                return $surcharge->issue_type;
            })
            ->addColumn('cost', function ($surcharge) {
                return $surcharge->cost;
            })
            ->addColumn('actions', function ($surcharge) {
                return $surcharge->action_buttons;
            })
            ->make(true);
    }

}
