<?php

namespace App\Http\Responses\Focus\equipment;

use Illuminate\Contracts\Support\Responsable;
use App\Models\customer\Customer;
use App\Models\branch\Branch;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\productcategory\Productcategory
     */
    protected $equipments;

    /**
     * @param App\Models\productcategory\Productcategory $productcategories
     */
    public function __construct($equipments)
    {
        $this->equipments = $equipments;
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
        $customers=Customer::all();
        $branches=Branch::all();
        return view('focus.equipments.edit')->with([
            'equipments' => $this->equipments,'customers'=>$customers,'branches'=>$branches
        ]);
    }
}