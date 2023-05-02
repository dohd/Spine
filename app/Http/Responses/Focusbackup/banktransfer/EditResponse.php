<?php

namespace App\Http\Responses\Focus\banktransfer;

use App\Models\account\Account;
use App\Models\banktransfer\Banktransfer;
use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\productcategory\Productcategory
     */
    protected $banktransfer;

    /**
     * @param App\Models\productcategory\Productcategory $productcategories
     */
    public function __construct($banktransfer)
    {
        $this->banktransfer = $banktransfer;
    }

    /**
     * To Response
     *
     * @param \App\Http\Requests\Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function toResponse($request)
    {
        $accounts = Account::whereHas('accountType', fn($q) => $q->where('system', 'bank'))->get(['id', 'holder']);
        $banktransfer_rel = Banktransfer::where('tid', $this->banktransfer->tid)->where('credit', '>', 0)->first();
        $pmt_mode = current(explode(' - ', $this->banktransfer->note));
        
        return view('focus.banktransfers.edit', compact('banktransfer_rel', 'accounts', 'pmt_mode'))
            ->with([ 'banktransfer' => $this->banktransfer]);
    }
}
