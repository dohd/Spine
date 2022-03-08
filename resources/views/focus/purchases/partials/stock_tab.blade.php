<div class="tab-pane active in" id="active1" aria-labelledby="active-tab1" role="tabpanel">
    <table class="table-responsive tfr my_stripe" id="stockTbl">
        <thead>
            <tr class="item_header bg-gradient-directional-blue white ">
                <th width="35%" class="text-center">{{trans('general.item_name')}}</th>
                <th width="10%" class="text-center">{{trans('general.quantity')}}</th>
                <th width="15%" class="text-center">{{trans('general.rate')}}</th>
                <th width="20%" class="text-center">{{trans('general.tax_p')}}</th>
                <th width="15%" class="text-center">{{trans('general.amount')}} ({{config('currency.symbol')}})</th>
                <th width="5%" class="text-center">Action</th>                   
            </tr>
        </thead>

        <tbody>
            <tr>
                <td><input type="text" class="form-control stockname" name="product_name[]" placeholder="Product Name" id='stockname-0' required></td>
                <td><input type="text" class="form-control qty" name="product_qty[]" id="qty-0" value="1" required></td>                    
                <td><input type="text" class="form-control price" name="product_price[]" id="price-0" required></td>
                <td>
                    <div class="row no-gutters">
                        <div class="col-6">
                            <select class="form-control rowtax" name="rowtax" id="rowtax-0">
                                @foreach ($additionals as $tax)
                                    <option value="{{ (int) $tax->value }}" {{ $tax->is_default ? 'selected' : ''}}>
                                        {{ $tax->name }}
                                    </option>
                                @endforeach                                                    
                            </select>
                        </div>
                        <div class="col-6">
                            <input type="text" class="form-control taxable" value="0">
                        </div>
                    </div>
                </td>
                <td class="text-center">{{config('currency.symbol')}} <b><span class='amount' id="result-0">0</span></b></td>              
                <td>
                    <button type="button" class="btn btn-danger d-none remove">Remove</button>
                </td>
            </tr>
            <tr>
                <td colspan=2>
                    <textarea id="product_desc-0" class="form-control" name="product_desc[]" placeholder="Product Description"></textarea>
                </td>
                <td colspan=4></td>
            </tr>

            <tr class="bg-white">
                <td>
                    <button type="button" class="btn btn-success" aria-label="Left Align" id="addstock">
                        <i class="fa fa-plus-square"></i> {{trans('general.add_row')}}
                    </button>
                </td>
                <td colspan="5"></td>
            </tr>
            <tr class="bg-white">
                <td colspan="4" align="right"><b>{{trans('general.total_tax')}}</b></td>                   
                <td align="left" colspan="2">
                    {{config('currency.symbol')}} <span id="invtax" class="lightMode">0</span>
                </td>
            </tr>
            <tr class="bg-white">
                <td colspan="4" align="right">
                    <b>Inventory Total ({{ config('currency.symbol') }})</b>
                </td>
                <td align="left" colspan="2">
                    <input type="text" class="form-control" name="stock_grandttl" value="0" id="stock_grandttl" readonly>
                    <input type="hidden" name="stock_subttl" id="stock_subttl" value="0">
                    <input type="hidden" name="stock_tax" id="stock_tax" value="0">
                </td>
            </tr>
        </tbody>
    </table>
</div>
