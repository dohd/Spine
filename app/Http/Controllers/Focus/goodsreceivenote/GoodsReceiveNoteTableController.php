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
namespace App\Http\Controllers\Focus\goodsreceivenote;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\goodsreceivenote\GoodsreceivenoteRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class GoodsReceiveNoteTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var GoodsreceivenoteRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param GoodsreceivenoteRepository $repository ;
     */
    public function __construct(GoodsreceivenoteRepository $repository)
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
        $prefixes = prefixesArray(['goods_received','purchase_order'], $ins);

        return Datatables::of($query)
            ->escapeColumns(['id'])
            ->addIndexColumn()    
            ->editColumn('tid', function ($grn) use ($prefixes){
                return gen4tid("{$prefixes[0]}-", $grn->tid);
            })
            ->filterColumn('tid', function($query, $tid) use($prefixes) {
                $arr = explode('-', $tid);
                if (strtolower($arr[0]) == strtolower($prefixes[0]) && isset($arr[1])) {
                    $query->where('tid', floatval($arr[1]));
                } elseif (floatval($tid)) {
                    $query->where('tid', floatval($tid));
                }
            })
            ->addColumn('supplier', function ($grn) {
                if ($grn->supplier)
                return $grn->supplier->name;
            })        
            ->addColumn('purchase_type', function ($grn) use ($prefixes){
                $po = $grn->purchaseorder;
                if ($po) {
                    $lpo_no = '<a href="'. route('biller.purchaseorders.show', $po) .'">'. gen4tid("{$prefixes[1]}-", $po->tid) .'</a>';
                    $note = $po->note;
                    return "({$lpo_no}) - {$note}";
                }
            })
            ->filterColumn('purchase_type', function($query, $type) use($prefixes) {
                $arr = explode('-', $type);
                if (strtolower($arr[0]) == strtolower($prefixes[1]) && isset($arr[1])) {
                    $query->whereHas('purchaseorder', fn($q) => $q->where('tid', floatval($arr[1])));
                } elseif (floatval($type)) {
                    $query->whereHas('purchaseorder', fn($q) => $q->where('tid', floatval($type)));
                }
            })
            ->editColumn('dnote', function ($grn) {
                return $grn->dnote;
            })
            ->editColumn('date', function ($grn) {
                return dateFormat($grn->date);
            })
            ->orderColumn('date', '-date $1')
            ->addColumn('actions', function ($grn) {
                return $grn->action_buttons;
            })
            ->make(true);
    }
}
