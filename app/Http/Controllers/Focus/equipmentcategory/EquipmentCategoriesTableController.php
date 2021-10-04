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
namespace App\Http\Controllers\Focus\equipmentcategory;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\equipmentcategory\EquipmentCategoryRepository;
use App\Http\Requests\Focus\equipmentcategory\ManageEquipmentCategoryRequest;

/**
 * Class BranchTableController.
 */
class EquipmentCategoriesTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $equipmentcategory;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $productcategory ;
     */
    public function __construct(EquipmentCategoryRepository $equipmentcategory)
    {

        $this->equipmentcategory = $equipmentcategory;
    }

    /**
     * This method return the data of the model
     * @param ManageProductcategoryRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageEquipmentCategoryRequest $request)
    {
    

        $core = $this->equipmentcategory->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
              
            ->addColumn('name', function ($equipmentcategory) {
                return '<a class="font-weight-bold" href="' . route('biller.equipmentcategories.index') . '?rel_type=' . $equipmentcategory->id . '&rel_id=' . $equipmentcategory->id . '">' . $equipmentcategory->name . '</a>';
            })
        
            // ->addColumn('location', function ($branch) {
                    //return $branch->location;
              //  })
         
            ->addColumn('created_at', function ($equipmentcategory) {
                return dateFormat($region->created_at);
            })
            ->addColumn('actions', function ($equipmentcategory) {
                return $equipmentcategory->action_buttons;
               // return '<a class="btn btn-purple round" href="' . route('biller.branches.index') . '?rel_type=' . $branch->id . '&rel_id=' . $branch->id . '" title="List"><i class="fa fa-list"></i></a>' . $branch->action_buttons;
            })
            ->make(true);
    }
}
