<?php

namespace App\Http\Responses\Focus\overtimepay;

use Illuminate\Contracts\Support\Responsable;
use App\Models\overtimerate\OvertimeRate;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\overtimepay\overtimepay
     */
    protected $overtimepay;

    /**
     * @param App\Models\overtimepay\overtimepay $overtimepays
     */
    public function __construct($overtimepay)
    {
        $this->overtimepay = $overtimepay;
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
        $overtimerates = OvertimeRate::all(['id','name']);
        return view('focus.overtimepay.edit')->with([
            'overtimepay' => $this->overtimepay,
            'overtimerates' => $overtimerates
        ]);
    }
}