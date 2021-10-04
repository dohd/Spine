<?php

namespace App\Http\Responses\Focus\assetequipment;

use Illuminate\Contracts\Support\Responsable;
use App\Models\account\Account;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\assetequipment\Assetequipment
     */
    protected $assetequipments;

    /**
     * @param App\Models\assetequipment\Assetequipment $assetequipments
     */
    public function __construct($assetequipments)
    {
        $this->assetequipments = $assetequipments;
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
         $accounts=Account::all();
          
        return view('focus.assetequipments.edit')->with([
            'assetequipments' => $this->assetequipments,'accounts'=>$accounts
        ]);
    }
}