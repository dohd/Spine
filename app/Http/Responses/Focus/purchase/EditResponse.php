<?php

namespace App\Http\Responses\Focus\purchase;
use App\Models\customfield\Customfield;
use App\Models\items\CustomEntry;
use App\Models\purchase\Purchase;
use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\purchaseorder\Purchaseorder
     */
    protected $purchase;

    /**
     * @param App\Models\purchaseorder\Purchaseorder $purchaseorders
     */
    public function __construct($purchase)
    {
        $this->purchase = $purchase;
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



 $items_tab_one = Purchase::where('tid', $this->purchase->tid)->where('bill_id', $this->purchase->id)->where('transaction_tab', 1)->get();
  $items_tab_two = Purchase::where('tid', $this->purchase->tid)->where('bill_id', $this->purchase->id)->where('transaction_tab', 2)->get();
   $items_tab_three = Purchase::where('tid', $this->purchase->tid)->where('bill_id', $this->purchase->id)->where('transaction_tab', 3)->get();
 

        return view('focus.purchases.edit')->with([
            'purchase' => $this->purchase,'purchase_items_tab_one'=> $items_tab_one, 'purchase_items_tab_two'=> $items_tab_two, 'purchase_items_tab_three'=> $items_tab_three])->with(bill_helper(3));
    }
}