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
use App\Repositories\Focus\projectstock\ProjectStockRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class ProjectStockTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProjectStockRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ProjectStockRepository $repository ;
     */
    public function __construct(ProjectStockRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @param Request $request
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        $query = $this->repository->getForDataTable();
        $ins = auth()->user()->ins;
        $prefixes = prefixesArray(['quote','proforma_invoice','stock_issuance'], $ins);

        return Datatables::of($query)
            ->escapeColumns(['id'])
            ->addIndexColumn()   
            ->editColumn('tid', function ($projectstock) use ($prefixes) {
                return gen4tid("{$prefixes[2]}-", $projectstock->tid);
            }) 
            ->filterColumn('tid', function($query, $tid) use($prefixes) {
                $arr = explode('-', $tid);
                if (strtolower($arr[0]) == strtolower($prefixes[2]) && isset($arr[1])) {
                    $query->where('tid', floatval($arr[1]));
                } elseif (floatval($tid)) {
                    $query->where('tid', floatval($tid));
                }
            })
            ->addColumn('quote', function ($projectstock) use ($prefixes) {
                $quote = $projectstock->quote;
                if ($quote) {
                    $tid = gen4tid($quote->bank_id? "{$prefixes[1]}-" : "{$prefixes[0]}-", $quote->tid);
                    return $tid . ' - ' . $quote->notes;
                }                
            })
            ->filterColumn('quote', function($query, $quote) use($prefixes) {
                $arr = explode('-', $quote);
                if (strtolower($arr[0]) == strtolower($prefixes[0]) && isset($arr[1])) {
                    $query->whereHas('quote', fn($q) => $q->where('tid', floatval($arr[1])));
                } elseif (floatval($quote)) {
                    $query->whereHas('quote', fn($q) => $q->where('tid', floatval($tid)));
                }
            })                   
            ->editColumn('date', function ($projectstock) {
                return dateFormat($projectstock->date);
            })
            ->orderColumn('date', '-date $1')
            ->addColumn('actions', function ($projectstock) {
                return $projectstock->action_buttons;
            })
            ->make(true);
    }
}
