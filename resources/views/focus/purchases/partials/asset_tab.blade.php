<div class="tab-pane" id="active3" aria-labelledby="link-tab3" role="tabpanel">
    <table class="table-responsive tfr my_stripe" id="assetTbl">
        <thead>
            <tr class="item_header bg-gradient-directional-success white">
                <th width="30%" class="text-center">{{trans('general.item_name')}}</th>
                <th width="8%" class="text-center">{{trans('general.quantity')}}</th>
                <th width="10%" class="text-center">{{trans('general.rate')}}</th>
                <th width="10%" class="text-center">{{trans('general.tax_p')}}</th>
                <th width="10%" class="text-center">{{trans('general.tax')}}</th>
                <th width="10%" class="text-center">{{trans('general.amount')}} ({{config('currency.symbol')}})</th>
                <th width="5%" class="text-center">{{trans('general.action')}}</th>
            </tr>
        </thead>
        <tbody>
            <!-- layout -->
            <tr>
                <td><input type="text" class="form-control assetname" name="name[]" placeholder="Asset Or Equipment"></td>
                <td><input type="text" class="form-control asset_qty" name="qty[]" value="1" id="assetqty-0"></td>
                <td><input type="text" class="form-control asset_price" name="rate[]" id="assetprice-0"></td>
                <td><input type="text" class="form-control asset_vat" name="itemtax[]" value="0" id="assetvat-0"></td>
                <td class="text-center"><span class="asset_tax">0</span></td>
                <td>{{config('currency.symbol')}} <b><span class='asset_amount'>0</span></b></td>
                <td><button type="button" class="btn btn-danger remove d-none">remove</button></td>
                <input type="hidden" id="assetitemid-0" name="item_id[]">
                <input type="hidden" class="assettaxr" name="taxrate[]">
                <input type="hidden" class="assetamountr" name="amount[]">
                <input type="hidden" class="assetitemprojectid" name="itemproject_id[]" value="0">
                <input type="hidden" name="type[]" value="Asset">
                <input type="hidden" name="id[]" value="0">
                <input type="hidden" name="uom[]">
            </tr>
            <tr>
                <td colspan="3"><textarea class="form-control descr" name="description[]" placeholder="Product Description" id="assetdescr-0"></textarea></td>
                <td colspan="4"></td>
            </tr>
            <!-- end layout -->
            
            <!-- fetched rows -->
            @isset ($purchase)
                @php ($i = 0)
                @foreach ($purchase->products as $item)
                    @if ($item->type == 'Asset')
                        <tr>
                            <td><input type="text" class="form-control assetname" name="name[]" value="{{ $item->asset->name }}" placeholder="Asset Or Equipment"></td>
                            <td><input type="text" class="form-control asset_qty" name="qty[]" value="{{ (int) $item->qty }}" id="assetqty-{{$i}}"></td>
                            <td><input type="text" class="form-control asset_price" name="rate[]" value="{{ (float) $item->rate }}" id="assetprice-{{$i}}"></td>
                            <td><input type="text" class="form-control asset_vat" name="itemtax[]" value="{{ (int) $item->itemtax }}" id="assetvat-{{$i}}"></td>
                            <td class="text-center"><span class="asset_tax">{{ (float) $item->taxrate }}</span></td>
                            <td>{{config('currency.symbol')}} <b><span class='asset_amount'>{{ (float) $item->amount }}</span></b></td>
                            <td><button type="button" class="btn btn-danger remove">remove</button></td>
                            <input type="hidden" id="assetitemid-{{$i}}" name="item_id[]" value="{{ $item->item_id }}">
                            <input type="hidden" class="assettaxr" name="taxrate[]" value="{{ (float) $item->taxrate }}">
                            <input type="hidden" class="assetamountr" name="amount[]" value="{{ (float) $item->amount }}">
                            <input type="hidden" class="assetitemprojectid" name="itemproject_id[]" value="0">
                            <input type="hidden" name="type[]" value="Asset">
                            <input type="hidden" name="id[]" value="{{ $item->id }}">
                            <input type="hidden" name="uom[]">
                        </tr>
                        <tr>
                            <td colspan="3">
                                <textarea class="form-control descr" name="description[]" placeholder="Product Description" id="assetdescr-{{$i}}">{{ $item->description }}</textarea>
                            </td>
                            <td colspan="4"></td>
                        </tr>
                        @php ($i++)
                    @endif
                @endforeach
            @endisset
            <!-- end fetched rows -->

            <tr class="bg-white">
                <td>
                    <button type="button" class="btn btn-success" aria-label="Left Align" id="addasset">
                        <i class="fa fa-plus-square"></i> {{trans('general.add_row')}}
                    </button>
                </td>
                <td colspan="6"></td>
            </tr>
            <tr class="bg-white">
                <td colspan="5" align="right"><b>{{trans('general.total_tax')}}</b></td>
                <td align="left" colspan="2">{{config('currency.symbol')}} <span id="assettaxrow">0</span></td>
            </tr>
            <tr class="bg-white">
                <td colspan="5" align="right">
                    <b>{{trans('general.grand_total')}} ({{config('currency.symbol')}})</b>
                </td>
                <td align="left" colspan="2">
                    <input type="text" class="form-control" name="asset_grandttl" value="0.00" id="asset_grandttl" readonly>
                    <input type="hidden" name="asset_tax" value="0.00" id="asset_tax">
                    <input type="hidden" name="asset_subttl" value="0.00" id="asset_subttl">
                </td>
            </tr>
        </tbody>
    </table>
</div>
