<div class="tab-pane active in" id="active1" aria-labelledby="active-tab1" role="tabpanel">
    <div id="saman-row">
        <table class="table-responsive tfr my_stripe">
            <thead>
                <tr class="item_header bg-gradient-directional-blue white ">
                    <th width="35%" class="text-center">{{trans('general.item_name')}}</th>
                    <th width="10%" class="text-center">{{trans('general.quantity')}}</th>
                    <th width="15%" class="text-center">{{trans('general.rate')}}</th>
                    <th width="20%" class="text-center">{{trans('general.tax_p')}}</th>
                    <th width="15%" class="text-center">{{trans('general.amount')}}
                        ({{config('currency.symbol')}})
                    </th>
                    <th width="5%" class="text-center">{{trans('general.action')}}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="text" class="form-control" name="product_name[]" placeholder="{{trans('general.enter_product')}}" id='productname-0'>
                    </td>
                    <td><input type="text" class="form-control req amnt" name="product_qty[]" id="amount-0" onkeypress="return isNumber(event)" onkeyup="rowTotal('0'), billUpyog()" autocomplete="off" value="1"><input type="hidden" id="alert-0" name="alert[]"></td>
                    <td><input type="text" class="form-control req prc" name="product_price[]" id="price-0" onkeypress="return isNumber(event)" onkeyup="rowTotal('0'), billUpyog()" autocomplete="off"></td>
                    <td>
                        <div class="input-group">
                            <select class="form-control" name="">
                                <option value="Factura No.">Factura No.</option>
                                <option value="Factura No.">Nota No.</option>
                            </select>
                            <input type="text" class="form-control" name="nuevaFactura" id="nuevaFactura" value="1-928361" readonly>
                        </div>
                    </td>

                    <td><span class="currenty">{{config('currency.symbol')}}</span>
                        <strong><span class='ttlText' id="result-0">0</span></strong>
                    </td>
                    <td class="text-center"></td>

                    <input type="hidden" name="total_tax[]" id="taxa-0" value="0">
                    <input type="hidden" name="total_discount[]" id="disca-0" value="0">
                    <input type="hidden" class="ttInput" name="product_subtotal[]" id="total-0" value="0">
                    <input type="hidden" class="pdIn" name="product_id[]" id="pid-0" value="0">
                    <input type="hidden" name="unit[]" id="unit-0">
                    <input type="hidden" name="code[]" id="hsn-0">
                    <input type="hidden" name="taxedvalue[]" id="taxedvalue-0">
                    <input type="hidden" name="salevalue[]" id="salevalue-0">
                </tr>
                <tr>
                    <td colspan="2"><textarea id="dpid-0" class="form-control html_editor" name="product_description[]" placeholder="{{trans('general.enter_description')}} (Optional)" autocomplete="off"></textarea><br></td>
                    <td colspan="2"><input type="text" class="form-control" name="project[]" placeholder="Search  Project By Project Name , Clent, Branch" id='project-0' disabled>
                        <input type="hidden" name="inventory_project_id[]" id="project_id-0">
                        <input type="hidden" name="client_id[]" id="client_id-0">
                        <input type="hidden" name="branch_id[]" id="branch_id-0">
                    </td>
                    <td colspan="2"><select class="form-control unit" data-uid="0" name="u_m[]" style="display: none">
                        </select></td>
                </tr>
                <tr class="last-item-row sub_c">
                    <td class="add-row">
                        <button type="button" class="btn btn-success" aria-label="Left Align" id="addproduct">
                            <i class="fa fa-plus-square"></i> {{trans('general.add_row')}}
                        </button>
                    </td>
                    <td colspan="4"></td>
                </tr>
                <tr class="sub_c" style="display: table-row;">
                    <td colspan="4" align="right">{{ Form::hidden('subtotal','0',['id'=>'subttlform']) }}
                        <strong>{{trans('general.total_tax')}}</strong>
                    </td>
                    <td align="left" colspan="2"><span class="currenty lightMode">{{config('currency.symbol')}}</span>
                        <span id="taxr" class="lightMode">0</span>
                    </td>
                    <input type="hidden" name="totalsaleamount" id="totalsaleamount">
                    <input type="hidden" name="totaltaxabe" id="totaltaxabe">
                    <input type="hidden" name="totaldiscount" id="totaldiscount">
                    <input type="hidden" name="totaltax" id="totaltax">
                </tr>

                <tr class="sub_c" style="display: table-row;">
                    <td colspan="4" align="right">
                        <strong>
                            Inventory Total
                            <span class="currenty lightMode">({{ config('currency.symbol') }})</span>
                        </strong>
                    </td>
                    <td align="left" colspan="2"><input type="text" name="total" class="form-control" id="invoiceyoghtml" readonly="">
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="row mt-3">
            <div class="col-12">{!! $fields !!}</div>
        </div>
    </div>
</div>