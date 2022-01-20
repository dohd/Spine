<?php

namespace App\Http\Controllers\Focus\lpo;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class LpoTableController extends Controller
{
    /**
     * Tracks lpo balance from quotes
     * @var float 
     */
    protected $balance;
    
    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = LpoController::getForDataTable();   
             
        return DataTables::of($core)
            ->addIndexColumn()
            ->addColumn('customer', function ($lpo) {
                return $lpo->customer->company . ' - ' . $lpo->branch->name;
            })
            ->addColumn('lpo_no', function ($lpo) {
                return $lpo->lpo_no;
            })
            ->addColumn('amount', function ($lpo) {
                $this->balance = $lpo->amount;
                return '<span><b>'.number_format($lpo->amount, 2).'</b></span>';
            })
            ->addColumn('invoiced', function ($lpo) {
                $tids = array(); 
                $total = 0;               
                foreach ($lpo->quotes as $quote) {
                    if ($quote->invoiced == 'Yes') {
                        $tid = sprintf('%04d', $quote->tid);
                        $tid = ($quote->bank_id) ? 'PI-'. $tid : $tid = 'QT-'. $tid;
                        $tids[] = '<a href="'. route('biller.quotes.show', $quote) .'"><b>'. $tid .'</b></a>';
                        $total += $quote->total;
                    }                    
                }

                $this->balance -= $total;
                if ($total) 
                    return '<span><b>'.number_format($total, 2).'</b><span><br>' . implode(', ', $tids);
            })
            ->addColumn('verified_uninvoiced', function ($lpo) {
                $tids = array(); 
                $total = 0;                 
                foreach ($lpo->quotes as $quote) {
                    if ($quote->verified == 'Yes' && $quote->invoiced == 'No') {
                        $tid = sprintf('%04d', $quote->tid);
                        $tid = ($quote->bank_id) ? 'PI-'. $tid : $tid = 'QT-'. $tid;
                        $tids[] = '<a href="'. route('biller.quotes.show', $quote) .'"><b>'. $tid .'</b></a>';
                        $total += $quote->total;
                    }                    
                }

                $this->balance -= $total;
                if ($total)
                    return '<span><b>'.number_format($total, 2).'</b><span><br>' . implode(', ', $tids);
            })
            ->addColumn('approved_unverified', function ($lpo) {
                $tids = array();
                $total = 0;                
                foreach ($lpo->quotes as $quote) {
                    if ($quote->status === 'approved' && $quote->verified === 'No') {
                        $tid = sprintf('%04d', $quote->tid);
                        $tid = ($quote->bank_id) ? 'PI-'. $tid : $tid = 'QT-'. $tid;
                        $tids[] = '<a href="'. route('biller.quotes.show', $quote) .'"><b>'. $tid .'</b></a>';
                        $total += $quote->total;
                    }                    
                }

                $this->balance -= $total;
                if ($total) 
                    return '<span><b>'.number_format($total, 2).'</b><span><br>' . implode(', ', $tids);
            })
            ->addColumn('balance', function ($lpo) {
                return '<span><b>'.number_format($this->balance, 2).'</b></span>';
            })
            ->addColumn('actions', function ($lpo) {
                return '<a href="'.$lpo->id.'" class="update-lpo" data-toggle="modal" data-target="#updateLpoModal"><i class="ft-edit fa-lg"></i></a>'
                    .'&nbsp;&nbsp;<a href="'.route('biller.lpo.destroy', $lpo->id).'" class="danger delete-lpo"><i class="fa fa-trash fa-lg"></i></a>';
            })
            ->rawColumns(['amount', 'invoiced', 'verified_uninvoiced', 'approved_unverified', 'balance', 'actions'])
            ->make(true);
    }
}
