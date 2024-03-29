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
namespace App\Http\Controllers\Focus\lead;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\lead\LeadRepository;
use App\Http\Requests\Focus\lead\ManageLeadRequest;

/**
 * Class BranchTableController.
 */
class LeadsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $lead;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $productcategory ;
     */
    public function __construct(LeadRepository $lead)
    {

        $this->lead = $lead;
    }

    /**
     * This method return the data of the model
     * @param ManageProductcategoryRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageLeadRequest $request)
    {
    

        $core = $this->lead->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()

             ->addColumn('reference', function ($lead) {
                return '<a class="font-weight-bold" href="' . route('biller.leads.index') . '?rel_type=' . $lead->id . '&rel_id=' . $lead->id . '">' . $lead->reference . '</a>';
            })


                ->addColumn('client_name', function ($lead) {


                     
                    switch ($lead->client_status) {
                        case 'customer':
                            return  $lead->customer->company.'   '. $lead->branch->name;
                            break;
                        case 'new':
                            return  $lead->client_name;
                            break;
                            
                    }
             



                  return $lead->client_name;
                })
           
            ->addColumn('created_at', function ($lead) {
                return dateFormat($lead->created_at);
            })
            ->addColumn('actions', function ($lead) {
                return $lead->action_buttons;
              
            })
            ->make(true);
    }
}
