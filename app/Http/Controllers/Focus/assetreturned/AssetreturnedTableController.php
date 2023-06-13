<?php

namespace App\Http\Controllers\Focus\assetreturned;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\assetreturned\AssetreturnedRepository;

class AssetreturnedTableController extends Controller
{
     /**
     * variable to store the repository object
     * @var assetreturnedRepository
     */
    protected $assetreturned;

    /**
     * contructor to initialize repository object
     * @param AssetreturnedRepository $assetorder ;
     */
    public function __construct(AssetreturnedRepository $assetreturned)
    {
        $this->assetreturned = $assetreturned;
    }

    /**
     * This method return the data of the model
     * @param Request $request
     *
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        $core = $this->assetreturned->getForDataTable();

        return Datatables::of($core)
            ->addIndexColumn()
            ->escapeColumns(['id'])
            ->addColumn('employee_id', function ($assetreturned) {
                return $assetreturned->employee_id;
            })
            ->addColumn('acquisition_number', function ($assetissuance) {
                return $assetissuance->acquisition_number;
            })
            ->addColumn('employee_name', function ($assetreturned) {
                $name = $assetreturned->employee_name;
                $link = route('biller.assetreturned.shows', [$assetreturned]);

                return $name;
            })
            ->addColumn('issue_date', function ($assetreturned) {
                return dateFormat($assetreturned->issue_date);
            })
            ->addColumn('return_date', function ($assetreturned) {
                return dateFormat($assetreturned->return_date);
            })
            ->addColumn('note', function ($assetreturned) {
                return $assetreturned->note;
            })
            ->addColumn('actions', function ($assetreturned) {
                return $assetreturned->action_buttons;
                // return '<a class="btn btn-purple round" href="' . route('biller.makepayment.single_payment', [$assetreturned->id]) . '" title="List">
                //     <i class="fa fa-cc-visa"></i></a>' . $assetreturned->action_buttons;
            })
            ->make(true);
    }

}
