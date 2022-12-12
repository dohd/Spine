<div class="tab-pane fade in" id="active1" aria-labelledby="active-tab1" role="tabpanel">
    <table class="table-responsive tfr my_stripe" id="stockTbl">
        <thead>
            <tr class="item_header bg-gradient-directional-blue white ">
                <th width="35%" class="text-center">{{trans('general.item_name')}}</th>
                <th width="10%" class="text-center">{{trans('general.quantity')}}</th>
                <th width="10%" class="text-center">UoM</th>
                <th width="15%" class="text-center">{{trans('general.rate')}}</th>
                <th width="10%" class="text-center">{{trans('general.tax_p')}}</th>
                <th width="10%" class="text-center">Tax</th>
                <th width="15%" class="text-center">{{trans('general.amount')}} ({{config('currency.symbol')}})</th>
                <th width="5%" class="text-center">Action</th>                   
            </tr>
        </thead>
        <tbody>
            <!-- layout -->
            <tr>
                <td><input type="text" class="form-control stockname" name="name[]" placeholder="Product Name" id='stockname-0' autocomplete="off"></td>
                <td><input type="text" class="form-control qty" name="qty[]" id="qty-0" value="1"></td>  
                <td><select name="uom[]" id="uom-0" class="form-control uom"></select></td>  
                <td><input type="text" class="form-control price" name="rate[]" id="price-0"></td>
                <td>
                    <select class="form-control rowtax" name="itemtax[]" id="rowtax-0">
                        @foreach ($additionals as $tax)
                            <option value="{{ (int) $tax->value }}" {{ $tax->is_default ? 'selected' : ''}}>
                                {{ $tax->name }}
                            </option>
                        @endforeach                                                    
                    </select>
                </td>
                <td><input type="text" class="form-control taxable" value="0" readonly></td>
                <td class="text-center">{{config('currency.symbol')}} <b><span class='amount' id="result-0">0</span></b></td>              
                <td><button type="button" class="btn btn-danger remove"><i class="fa fa-minus-square"></i></button></td>
                <input type="hidden" id="stockitemid-0" name="item_id[]">
                <input type="hidden" class="stocktaxr" name="taxrate[]">
                <input type="hidden" class="stockamountr" name="amount[]">
                <input type="hidden" name="type[]" value="Stock">
                <input type="hidden" name="id[]" value="0">
            </tr>
            <tr>
                <td colspan="3">
                    <textarea id="stockdescr-0" class="form-control descr" name="description[]" placeholder="Product Description"></textarea>
                </td>
                <td>
                    <select name="warehouse_id[]" class="form-control warehouse" id="warehouseid">
                        <option value="">-- Warehouse --</option>
                        @foreach ($warehouses as $row)
                            <option value="{{ $row->id }}">{{ $row->title }}</option>
                        @endforeach
                    </select>
                </td>
                <td colspan="4">
                    <input type="text" class="form-control projectstock" id="projectstocktext-0" placeholder="Search Project By Name">
                    <input type="hidden" name="itemproject_id[]" id="projectstockval-0">
                </td>
            </tr>
            <!-- end layout -->

            <!-- fetched rows -->
            @isset ($purchase)
                @php ($i = 0)
                @foreach ($purchase->products as $item)
                    @if ($item->type == 'Stock')
                        <tr>
                            <td><input type="text" class="form-control stockname" name="name[]" value="{{ $item->description }}" placeholder="Product Name" id='stockname-{{$i}}'></td>
                            <td><input type="text" class="form-control qty" name="qty[]" value="{{ number_format($item->qty, 1) }}" id="qty-{{$i}}"></td>    
                            <td>
                                <select name="uom[]" id="uom-{{ $i }}" class="form-control uom">
                                    <option value="{{ $item->uom }}">{{ $item->uom }}</option>
                                </select>
                            </td>                  
                            <td><input type="text" class="form-control price" name="rate[]" value="{{ numberFormat($item->rate) }}" id="price-{{$i}}"></td>
                            <td>
                                <select class="form-control rowtax" name="itemtax[]" id="rowtax-{{$i}}">
                                    @foreach ($additionals as $tax)
                                        <option value="{{ (int) $tax->value }}" {{ $tax->value == $item->itemtax ? 'selected' : ''}}>
                                            {{ $tax->name }}
                                        </option>
                                    @endforeach                                                    
                                </select>
                            </td>
                            <td><input type="text" class="form-control taxable" value="{{ (float) $item->taxrate }}" readonly></td>
                            <td class="text-center">{{config('currency.symbol')}} <b><span class='amount' id="result-{{$i}}">{{ (float) $item->amount }}</span></b></td>              
                            <td><button type="button" class="btn btn-danger remove"><i class="fa fa-minus-square"></i></button></td>
                            <input type="hidden" id="stockitemid-{{$i}}" name="item_id[]" value="{{ $item->item_id }}">
                            <input type="hidden" class="stocktaxr" name="taxrate[]" value="{{ (float) $item->taxrate }}">
                            <input type="hidden" class="stockamountr" name="amount[]" value="{{ (float) $item->amount }}">
                            <input type="hidden" name="type[]" value="Stock">
                            <input type="hidden" name="id[]" value="{{ $item->id }}">
                        </tr>
                        <tr>
                            <td colspan=3>
                                <textarea id="stockdescr-{{$i}}" class="form-control descr" name="description[]" placeholder="Product Description">{{ $item->description }}</textarea>
                            </td>
                            <td>
                                <select name="warehouse_id[]" class="form-control warehouse" id="warehouseid-{{$i}}">
                                    <option value="">-- Warehouse --</option>
                                    @foreach ($warehouses as $row)
                                        <option value="{{ $row->id }}" {{ $row->id == $item->warehouse_id? 'selected' : '' }}>
                                            {{ $row->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td colspan="4">
                                <input type="text" class="form-control projectstock" value="{{ $item->project ? $item->project->name : '' }}" id="projectstocktext-0" placeholder="Search Project By Name">
                                <input type="hidden" name="itemproject_id[]" value="{{ $item->itemproject_id }}" id="projectstockval-{{$i}}">
                            </td>
                        </tr>
                        @php ($i++)
                    @endif
                @endforeach
            @endisset
            <!-- end fetched rows -->

            <tr class="bg-white">
                <td>
                    <button type="button" class="btn btn-success" aria-label="Left Align" id="addstock">
                        <i class="fa fa-plus-square"></i> {{trans('general.add_row')}}
                    </button>
                </td>
                <td colspan="6"></td>
            </tr>
            <tr class="bg-white">
                <td colspan="5" align="right"><b>{{trans('general.total_tax')}}</b></td>                   
                <td align="left" colspan="2">
                    {{config('currency.symbol')}} <span id="invtax" class="lightMode">0</span>
                </td>
            </tr>
            <tr class="bg-white">
                <td colspan="5" align="right">
                    <b>Inventory Total ({{ config('currency.symbol') }})</b>
                </td>
                <td align="left" colspan="2">
                    <input type="text" class="form-control" name="stock_grandttl" value="0.00" id="stock_grandttl" readonly>
                    <input type="hidden" name="stock_subttl" value="0.00" id="stock_subttl">
                    <input type="hidden" name="stock_tax" value="0.00" id="stock_tax">
                </td>
            </tr>
        </tbody>
    </table>
</div>
