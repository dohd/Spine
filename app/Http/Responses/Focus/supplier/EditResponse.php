<?php

namespace App\Http\Responses\Focus\supplier;

use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\supplier\Supplier
     */
    protected $supplier;

    /**
     * @param App\Models\supplier\Supplier $supplier
     */
    public function __construct($supplier)
    {
        $this->supplier = $supplier;
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
        return view('focus.suppliers.edit')->with([
            'supplier' => $this->supplier
        ]);
    }
}