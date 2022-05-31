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
namespace App\Http\Controllers\Focus\issuance;

use App\Http\Controllers\Controller;
use App\Models\issuance\Issuance;
use App\Repositories\Focus\quote\QuoteRepository;
use Yajra\DataTables\Facades\DataTables;

/**
 * Class QuotesTableController.
 */
class IssuanceTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var QuoteRepository
     */
    protected $quote;
    protected $issuance;


    /**
     * contructor to initialize repository object
     * @param QuoteRepository $quote ;
     */
    public function __construct(QuoteRepository $quote)
    {
        $this->quote = $quote;
        $this->issuance = new Issuance;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {        
        $core = $this->quote->getForVerifyDataTable();
        
        return Datatables::of($core)
            ->addIndexColumn()
            ->escapeColumns(['id'])
            ->addColumn('project_tid', function($quote) {
                if (isset($quote->project_quote->project)) {
                    $no = $quote->project_quote->project->tid;
                    return gen4tid('Prj-', $no);
                }
            })
            ->addColumn('tid', function ($quote) {
                if ($quote->bank_id) return gen4tid('PI-', $quote->tid);
                return gen4tid('QT-', $quote->tid);               
            })            
            ->addColumn('customer', function ($quote) {
                if (isset($quote->customer) && isset($quote->branch)) 
                    return $quote->customer->name.' - '.$quote->branch->name;
            })
            ->addColumn('date', function ($quote) {
                return dateFormat($quote->date);
            })
            ->addColumn('status', function ($quote) {
                return $quote->issuance_status . ':';
            })
            ->addColumn('actions', function ($quote) {
                $check_button = '<a href="'. route('biller.issuance.create', 'id='.$quote->id) .'" class="btn btn-success round" data-toggle="tooltip" data-placement="bottom" title="Issue">
                    <i class="fa fa-check"></i></a>';
                if ($quote->issuance->count()) {
                    $this->issuance->id = $quote->issuance->first()->id;
                    // if project closed, return view button
                    if ($quote->closed_by) return $this->issuance->action_buttons;
                    // return check button and view button
                    return $check_button . $this->issuance->action_buttons;
                }
                // no issuance, return check button
                return $check_button;
            })
            ->make(true);
    }
}