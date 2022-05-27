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
namespace App\Http\Controllers\Focus\quote;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\quote\QuoteRepository;

/**
 * Class QuotesTableController.
 */
class QuotesTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var QuoteRepository
     */
    protected $quote;

    /**
     * contructor to initialize repository object
     * @param QuoteRepository $quote ;
     */
    public function __construct(QuoteRepository $quote)
    {
        $this->quote = $quote;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->quote->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('lead_tid', function($quote) {
                return 'Tkt-' . sprintf('%04d', $quote->lead->reference);
            })
            ->addColumn('tid', function ($quote) {
                $tid = sprintf('%04d', $quote->tid);
                $tid = ($quote->bank_id) ? 'PI-'.$tid : 'QT-'.$tid;
                if ($quote->revision) $tid .= $quote->revision; 
                              
                $link = route('biller.quotes.show', [$quote->id]);
                if ($quote->bank_id) $link = route('biller.quotes.show', [$quote->id, 'page=pi']);

                return '<a class="font-weight-bold" href="' . $link . '">' . $tid . '</a>';
            })
            ->addColumn('customer', function ($quote) {
                $client_name = $quote->customer ? $quote->customer->name : '';
                $branch_name = $quote->branch ? $quote->branch->name : '';
                if ($client_name && $branch_name) 
                    return $client_name . ' - ' . $branch_name . ' <a class="font-weight-bold" href="'.route('biller.customers.show', [$quote->customer->id]).'"><i class="ft-eye"></i></a>';

                return $quote->lead->client_name;
            })
            ->addColumn('date', function ($quote) {
                return dateFormat($quote->date);
            })
            ->addColumn('total', function ($quote) {
                return number_format($quote->total, 2);
            })
            ->addColumn('status', function ($quote) {
                $statuses = array('approved', 'client_approved', 'cancelled', 'pending');
                $backgrds = array('bg-primary', 'bg-success', 'bg-danger', 'bg-secondary');
                $backgrd = $backgrds[array_search($quote->status, $statuses)];
                $lpo = $quote->lpo ? 'LPO: ' . $quote->lpo->lpo_no : 'NULL:';

                return '<span class="badge ' . $backgrd . '">' . $quote->status . ':</span><br>'. $lpo;
            })
            ->addColumn('verified', function ($quote) {
                $tid = 'NIL:';
                if (isset($quote->invoice_product->invoice)) {
                    $tid = gen4tid('Inv-', $quote->invoice_product->invoice->tid);
                    $proj_no = gen4tid('Prj-', $quote->project_quote->project->tid);
                    if ($quote->closed_by) $tid .= ' closed-' . $proj_no;
                    else $tid .= ' open-' . $proj_no;
                }
                
                return $quote->verified . ':; <br>' . $tid;
            })
            ->addColumn('actions', function ($quote) {
                $action_buttons = $quote->action_buttons;
                if (request('page') == 'pi') {
                    $name = 'biller.quotes.show';
                    $action_buttons = str_replace(route($name, $quote), route($name, [$quote, 'page=pi']), $action_buttons);
                }
                $valid_token = token_validator('', 'q'.$quote->id .$quote->tid, true);
                $copy_text = $quote->bank_id ? 'PI Copy' : 'Quote Copy';
                $task = $quote->bank_id ? 'page=pi&task=pi_to_pi' : 'task=quote_to_quote';

                return '<a href="'.route('biller.print_quote', [$quote->id, 4, $valid_token, 1]).'" class="btn btn-purple round" target="_blank" data-toggle="tooltip" data-placement="top" title="Print"><i class="fa fa-print"></i></a> '
                    .'<a href="'.route('biller.quotes.edit', [$quote, $task]).'" class="btn btn-warning round" data-toggle="tooltip" data-placement="top" title="'. $copy_text .'"><i class="fa fa-clone" aria-hidden="true"></i></a> '
                    .$action_buttons;
            })
            ->make(true);
    }
}
