<div class="tab-pane" id="active2" aria-labelledby="link-tab2" role="tabpanel">
    <table class="table-responsive tfr my_stripe" id="expTbl">
        <thead>
            <tr class="item_header bg-gradient-directional-danger white">
                <th width="30%" class="text-center">Ledger Name</th>
                <th width="8%" class="text-center">{{trans('general.quantity')}}</th>
                <th width="10%" class="text-center">{{trans('general.rate')}}</th>
                <th width="10%" class="text-center">{{trans('general.tax_p')}}</th>
                <th width="8%" class="text-center">Tax</th>
                <th width="10%" class="text-center">{{trans('general.amount')}} ({{config('currency.symbol')}})</th>
                <th width="5%" class="text-center">{{trans('general.action')}}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><input type="text" class="form-control" name="ledger_name[]" placeholder="Enter Ledger" id='ledgername-0'></td>
                <td><input type="text" class="form-control exp_qty" name="exp_product_qty[]" id="expqty-0" value="1"></td>
                <td><input type="text" class="form-control exp_price" name="exp_product_price[]" id="expprice-0"></td>
                <td><input type="text" class="form-control exp_vat " name="exp_product_tax[]" id="expvat-0" value="0"></td>
                <td class="text-center"><span class="exp_tax" id="exptax-0">0</span></td>
                <td>{{config('currency.symbol')}} <b><span class="exp_amount" id="expamount-0">0</span></b></td>
                <td><button type="button" class="btn btn-danger remove d-none">remove</button></td>
            </tr>
            <tr>
                <td colspan="3">
                    <textarea id="exp_descr-0" class="form-control" name="exp_product_description[]" placeholder="Enter Description"></textarea>
                </td>
                <td colspan="4">
                    <input type="text" class="form-control" name="exp_project[]" placeholder="Search  Project" id='expproject-0'>
                </td>
            </tr>
            <tr class="bg-white">
                <td>
                    <button type="button" class="btn btn-success" aria-label="Left Align" id="addexp">
                        <i class="fa fa-plus-square"></i> {{trans('general.add_row')}}
                    </button>
                </td>
                <td colspan="6"></td>
            </tr>
            <tr class="bg-white">
                <td colspan="5" align="right"><b>{{trans('general.total_tax')}}</b></td>
                <td align="left" colspan="2">{{config('currency.symbol')}} <span id="exprow_taxttl" class="lightMode">0</span></td>
            </tr>
            <tr class="bg-white">
                <td colspan="5" align="right"><b>{{trans('general.grand_total')}} ({{config('currency.symbol')}})</b></td>
                <td align="left" colspan="2">
                    <input type="text" class="form-control" name="expense_grandttl" value="0.00" id="exp_grandttl" readonly>
                    <input type="hidden" name="expense_subttl" value="0.00" id="exp_subttl">
                    <input type="hidden" name="expense_tax" value="0.00" id="exp_tax">
                </td>
            </tr>
        </tbody>
    </table>
</div>
