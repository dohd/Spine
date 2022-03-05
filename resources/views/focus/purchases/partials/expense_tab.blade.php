<div class="tab-pane" id="active2" aria-labelledby="link-tab2" role="tabpanel">
    <div id="saman-row-exp">
        <table class="table-responsive tfr my_stripe">
            <thead>
                <tr class="item_header bg-gradient-directional-danger white">
                    <th width="30%" class="text-center">Ledger Name</th>
                    <th width="8%" class="text-center">{{trans('general.quantity')}}</th>
                    <th width="10%" class="text-center">{{trans('general.rate')}}</th>
                    <th width="10%" class="text-center">{{trans('general.tax_p')}}</th>
                    <th width="10%" class="text-center">{{trans('general.tax')}}</th>
                    <th width="7%" class="text-center">{{trans('general.discount')}}</th>
                    <th width="10%" class="text-center">{{trans('general.amount')}}
                        ({{config('currency.symbol')}})
                    </th>
                    <th width="5%" class="text-center">{{trans('general.action')}}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="text" class="form-control" name="ledger_name[]" placeholder="Enter Ledger" id='ledgername-0'>
                    </td>
                    <td><input type="text" class="form-control req exp_amnt" name="exp_product_qty[]" id="exp_amount-0" onkeypress="return isNumber(event)" onkeyup="expRowTotal('0'), expBillUpyog()" autocomplete="off" value="1">
                    </td>
                    <td><input type="text" class="form-control req exp_prc" name="exp_product_price[]" id="exp_price-0" onkeypress="return isNumber(event)" onkeyup="expRowTotal('0'), expBillUpyog()" autocomplete="off"></td>
                    <td><input type="text" class="form-control exp_vat " name="exp_product_tax[]" id="exp_vat-0" onkeypress="return isNumber(event)" onkeyup="expRowTotal('0'), expBillUpyog()" autocomplete="off"></td>
                    <td class="text-center" id="exp_texttaxa-0">0</td>
                    <td><input type="text" class="form-control exp_discount" name="exp_product_discount[]" onkeypress="return isNumber(event)" id="exp_discount-0" onkeyup="expRowTotal('0'), expBillUpyog()" autocomplete="off"></td>
                    <td><span class="exp_currenty">{{config('currency.symbol')}}</span>
                        <strong><span class='exp_ttlText' id="exp_result-0">0</span></strong>
                    </td>
                    <td class="text-center">
                    </td>
                    <input type="hidden" name="exp_total_tax[]" id="exp_taxa-0" value="0">
                    <input type="hidden" name="exp_total_discount[]" id="exp_disca-0" value="0">
                    <input type="hidden" class="exp_ttInput" name="exp_product_subtotal[]" id="exp_total-0" value="0">
                    <input type="hidden" class="exp_pdIn" name="ledger_id[]" id="exp_pid-0" value="0">
                    <input type="hidden" name="exp_taxedvalue[]" id="exp_taxedvalue-0">
                    <input type="hidden" name="exp_salevalue[]" id="exp_salevalue-0">
                </tr>
                <tr>
                    <td colspan="3"><textarea id="exp_dpid-0" class="form-control html_editor" name="exp_product_description[]" placeholder="{{trans('general.enter_description')}} (Optional)" autocomplete="off"></textarea><br></td>
                    <td colspan="4"><input type="text" class="form-control" name="exp_project[]" placeholder="Search  Project By Project Name , Clent, Branch" id='exp_project-0'>
                        <input type="hidden" name="exp_project_id[]" id="exp_project_id-0">
                        <input type="hidden" name="exp_client_id[]" id="exp_client_id-0">
                        <input type="hidden" name="exp_branch_id[]" id="exp_branch_id-0">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                <tr class="last-item-row-exp sub_c">
                    <td class="add-row">
                        <button type="button" class="btn btn-success" aria-label="Left Align" id="expaddproduct">
                            <i class="fa fa-plus-square"></i> {{trans('general.add_row')}}
                        </button>
                    </td>
                    <td colspan="7"></td>
                </tr>
                <tr class="sub_c" style="display: table-row;">
                    <td colspan="6" align="right">{{ Form::hidden('subtotal','0',['id'=>'exp_subttlform']) }}
                        <strong>{{trans('general.total_tax')}}</strong>
                    </td>
                    <td align="left" colspan="2"><span class="currenty lightMode">{{config('currency.symbol')}}</span>
                        <span id="exp_taxr" class="lightMode">0</span>
                        <input type="hidden" name="exp_totalsaleamount" id="exp_totalsaleamount">
                        <input type="hidden" name="exp_totaltaxabe" id="exp_totaltaxabe">
                        <input type="hidden" name="exp_totaldiscount" id="exp_totaldiscount">
                        <input type="hidden" name="exp_totaltax" id="exp_totaltax">
                    </td>
                </tr>
                <tr class="sub_c" style="display: table-row;">
                    <td colspan="6" align="right">
                        <strong>{{trans('general.total_discount')}}</strong>
                    </td>
                    <td align="left" colspan="2"><span class="currenty lightMode"></span>
                        <span id="exp_discs" class="lightMode">0</span>
                    </td>
                </tr>
                <tr class="sub_c" style="display: table-row;">
                    <td colspan="6" align="right"><strong>{{trans('general.grand_total')}}
                            (<span class="currenty lightMode">{{config('currency.symbol')}}</span>)</strong>
                    </td>
                    <td align="left" colspan="2"><input type="text" name="exp_total" class="form-control" id="exp_invoiceyoghtml" readonly="">
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="row mt-3">
            <div class="col-12">{!! $fields !!}</div>
        </div>
    </div>
</div>