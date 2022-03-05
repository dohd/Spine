<div class="tab-pane" id="active3" aria-labelledby="link-tab3" role="tabpanel">
    <div id="saman-row-item">
        <table class="table-responsive tfr my_stripe">
            <thead>
                <tr class="item_header bg-gradient-directional-success white">
                    <th width="30%" class="text-center">{{trans('general.item_name')}}</th>
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
                    <td><input type="text" class="form-control" name="item_name[]" placeholder="Enter Assets Or Equipments Name" id='itemname-0'>
                    </td>
                    <td><input type="text" class="form-control req item_amnt" name="item_product_qty[]" id="item_amount-0" onkeypress="return isNumber(event)" onkeyup="itemRowTotal('0'), itemBillUpyog()" autocomplete="off" value="1">
                    </td>
                    <td><input type="text" class="form-control req item_prc" name="item_product_price[]" id="item_price-0" onkeypress="return isNumber(event)" onkeyup="itemRowTotal('0'), itemBillUpyog()" autocomplete="off"></td>
                    <td><input type="text" class="form-control item_vat " name="item_product_tax[]" id="item_vat-0" onkeypress="return isNumber(event)" onkeyup="itemRowTotal('0'), itemBillUpyog()" autocomplete="off"></td>
                    <td class="text-center" id="item_texttaxa-0">0</td>
                    <td><input type="text" class="form-control item_discount" name="item_product_discount[]" onkeypress="return isNumber(event)" id="item_discount-0" onkeyup="itemRowTotal('0'), itemBillUpyog()" autocomplete="off"></td>
                    <td><span class="item_currenty">{{config('currency.symbol')}}</span>
                        <strong><span class='item_ttlText' id="item_result-0">0</span></strong>
                    </td>
                    <td class="text-center">
                    </td>
                    <input type="hidden" name="item_total_tax[]" id="item_taxa-0" value="0">
                    <input type="hidden" name="item_total_discount[]" id="item_disca-0" value="0">
                    <input type="hidden" class="item_ttInput" name="item_product_subtotal[]" id="item_total-0" value="0">
                    <input type="hidden" class="item_pdIn" name="item_id[]" id="item_pid-0" value="0">
                    <input type="hidden" name="account_id[]" id="account_id-0" value="0">
                    <input type="hidden" name="account_type[]" id="account_type-0">
                    <input type="hidden" name="item_taxedvalue[]" id="item_taxedvalue-0">
                    <input type="hidden" name="item_salevalue[]" id="item_salevalue-0">
                </tr>
                <tr>
                    <td colspan="3"><textarea id="item_dpid-0" class="form-control html_editor" name="item_product_description[]" placeholder="{{trans('general.enter_description')}} (Optional)" autocomplete="off"></textarea><br></td>
                    <td colspan="5"><input type="text" class="form-control" name="item_project[]" placeholder="Search  Project By Project Name , Clent, Branch" id='item_project-0' disabled>
                        <input type="hidden" name="item_project_id[]" id="item_project_id-0">
                        <input type="hidden" name="item_client_id[]" id="item_client_id-0">
                        <input type="hidden" name="item_branch_id[]" id="item_branch_id-0">
                    </td>
                </tr>
                <tr class="last-item-row-item sub_c">
                    <td class="add-row">
                        <button type="button" class="btn btn-success" aria-label="Left Align" id="itemaddproduct">
                            <i class="fa fa-plus-square"></i> {{trans('general.add_row')}}
                        </button>
                    </td>
                    <td colspan="7"></td>
                </tr>
                <tr class="sub_c" style="display: table-row;">
                    <td colspan="6" align="right">{{ Form::hidden('subtotal','0',['id'=>'item_subttlform']) }}
                        <strong>{{trans('general.total_tax')}}</strong>
                    </td>
                    <td align="left" colspan="2"><span class="currenty lightMode">{{config('currency.symbol')}}</span>
                        <span id="item_taxr" class="lightMode">0</span>
                        <input type="hidden" name="item_totalsaleamount" id="item_totalsaleamount">
                        <input type="hidden" name="item_totaltaxabe" id="item_totaltaxabe">
                        <input type="hidden" name="item_totaldiscount" id="item_totaldiscount">
                        <input type="hidden" name="item_totaltax" id="item_totaltax">
                    </td>
                </tr>
                <tr class="sub_c" style="display: table-row;">
                    <td colspan="6" align="right">
                        <strong>{{trans('general.total_discount')}}</strong>
                    </td>
                    <td align="left" colspan="2"><span class="currenty lightMode"></span>
                        <span id="item_discs" class="lightMode">0</span>
                    </td>
                </tr>
                <tr class="sub_c" style="display: table-row;">
                    <td colspan="6" align="right"><strong>{{trans('general.grand_total')}}
                            (<span class="currenty lightMode">{{config('currency.symbol')}}</span>)</strong>
                    </td>
                    <td align="left" colspan="2"><input type="text" name="item_total" class="form-control" id="item_invoiceyoghtml" readonly="">
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="row mt-3">
            <div class="col-12">{!! $fields !!}</div>
        </div>
    </div>
</div>
