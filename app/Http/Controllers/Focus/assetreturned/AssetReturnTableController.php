<?php

namespace App\Http\Controllers\Focus\assetissuance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\assetissuance\AssetissuanceRepository;

class AssetReturnTableController extends Controller
{
     /**
     * variable to store the repository object
     * @var assetissuanceRepository
     */
    protected $assetissuance;

    /**
     * contructor to initialize repository object
     * @param AssetissuanceRepository $assetorder ;
     */
    public function __construct(AssetissuanceRepository $assetissuance)
    {
        $this->assetissuance = $assetissuance;
    }

    /**
     * This method return the data of the model
     * @param Request $request
     *
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        $core = $this->assetissuance->getForDataTable();

        return Datatables::of($core)
            ->addIndexColumn()
            ->escapeColumns(['id'])
            ->addColumn('employee_id', function ($assetissuance) {
                return $assetissuance->employee_id;
            })
            ->addColumn('employee_name', function ($assetissuance) {
                $name = $assetissuance->employee_name;
                $link = route('biller.assetissuance.shows', [$assetissuance]);
                //. ' <a class="font-weight-bold" href="' . $link . '"><i class="ft-eye"></i></a>'
                return $name;
            })
            ->addColumn('issue_date', function ($assetissuance) {
                return dateFormat($assetissuance->issue_date);
            })
            ->addColumn('return_date', function ($assetissuance) {
                return dateFormat($assetissuance->return_date);
            })
            ->addColumn('note', function ($assetissuance) {
                return $assetissuance->note;
            })
            ->addColumn('actions', function ($assetissuance) {
                return $assetissuance->action_buttons;
                // return '<a class="btn btn-purple round" href="' . route('biller.makepayment.single_payment', [$assetissuance->id]) . '" title="List">
                //     <i class="fa fa-cc-visa"></i></a>' . $assetissuance->action_buttons;
            })
            ->make(true);
    }

}