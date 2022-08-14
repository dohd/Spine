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
namespace App\Http\Controllers\Focus\projectstock;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\quote\QuoteRepository;

class QuoteTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var QuoteRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param QuoteRepository $repository ;
     */
    public function __construct(QuoteRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->repository->getForVerifyDataTable();
    
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('checkbox', function ($quote) {
                return '<input type="checkbox" class="select-row" value="'. $quote->id .'">';
            })
            ->addColumn('date', function ($quote) {
                return dateFormat($quote->date);
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
                    return $client_name . ' - ' . $branch_name 
                        . ' <a class="font-weight-bold" href="'. route('biller.customers.show', [$quote->customer->id]) .'"><i class="ft-eye"></i></a>';

                return $quote->lead->client_name;
            })
            ->addColumn('item_count', function ($quote) {
                return $quote->products->count();
            })
            ->addColumn('issue_count', function ($quote) {
                return 0;
            })
            ->addColumn('issue_status', function ($quote) {
                return 'pending';
            })
            ->make(true);
    }
}
