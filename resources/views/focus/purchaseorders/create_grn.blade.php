@extends ('core.layouts.app')

@section ('title', 'Purchase Order | Grn')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-1">
            <h4>Receive Purchase Order Goods</h4>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.purchaseorders.partials.purchaseorders-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">  
            @php ($po = $purchaseorder)
            {{ Form::open(['route' => ['biller.purchaseorders.grn', $po], 'method' => 'POST']) }}          
                <div class="row mb-1">
                    <div class="col-6">
                        <div>
                            <label for="lpo_no">LPO Number</label>
                            <input type="text" class="form-control" name="lpo_no" value="{{ $po->tid }}" disabled>
                        </div>  
                    </div>
                    <div class="col-6">                                        
                        <div>
                            <label for="supplier">Supplier</label>
                            <input type="text" class="form-control" name="supplier" value="{{ $po->supplier->name }}" disabled>
                        </div>                        
                    </div>
                </div>
                <div class="row mb-1">
                    <div class="col-6">
                        <div>
                            <label for="project">Project</label>
                            <input type="text" class="form-control" name="project" value="{{ $po->project->name }}" disabled>
                        </div>
                    </div>
                    <div class="col-6">                                        
                        <div>
                            <label for="note">Note</label>
                            <input type="text" class="form-control" name="note" value="{{ $po->note }}" disabled>
                        </div>                        
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-4">
                        <input type="hidden" name="grandtax" id="grandtax">
                        <input type="hidden" name="grandttl" id="grandttl">
                        <input type="hidden" name="paidttl" id="paidttl">
                    </div>
                    <div class="col-4">
                        {{ Form::submit('Receive Goods', ['class' => 'btn btn-primary btn-lg block']) }}
                    </div>
                </div>

                <!-- tab menu -->
                <ul class="nav nav-tabs nav-top-border no-hover-bg nav-justified" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link " id="active-tab1" data-toggle="tab" href="#active1" aria-controls="active1" role="tab">
                            Inventory / Stock
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " id="active-tab2" data-toggle="tab" href="#active2" aria-controls="active2" role="tab">
                            Expenses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " id="active-tab3" data-toggle="tab" href="#active3" aria-controls="active3" role="tab">
                            Asset & Equipments
                        </a>
                    </li>
                </ul>
                <!-- stock tab -->
                <div class="tab-content px-1 pt-1">
                    <div class="tab-pane active in" id="active1" aria-labelledby="customer-details" role="tabpanel">
                        <table class="table-responsive tfr" width="100%" id="stockTbl">
                            <thead>
                                <tr class="item_header bg-gradient-directional-blue white">
                                    <th width="6%" class="text-center">#</th>
                                    <th width="35%" class="text-center">Product Description</th>
                                    <th width="10%" class="text-center">PO Qty</th>
                                    <th width="10%">Qty Received</th>
                                    <th width="10%" class="text-center">Qty</th>
                                    <th width="10%" class="text-center">DNote</th>                            
                                    <th width="16%" class="text-center">Date</th>
                                </tr>                                
                            </thead>
                            <tbody>
                                @php ($i = 0)
                                @foreach ($po->products as $item)
                                    @if ($item->type == 'Stock')
                                        <tr>
                                            <td class="text-center">{{$i + 1}}</td>
                                            <td><textarea name="description" cols="50" rows="3" disabled>{{ $item->description }}</textarea></td>
                                            <td><input type="text" class="form-control" value="{{ (float) $item->qty }}" disabled></td>
                                            <td><input type="text" class="form-control" name="grn_qty[]" value="{{ $item->grn_items->sum('qty') }}" readonly></td>
                                            <td><input type="number" step=".01" class="form-control qty" name="qty[]"></td>
                                            <td><input type="text" class="form-control" name="dnote[]"></td>
                                            <td><input type="text" class="form-control datepicker" name="date[]"></td>
                                            <input type="hidden" name="poitem_id[]" value="{{ $item->id }}">
                                            <input type="hidden" class="porate" name="poitem_rate[]" value="{{ $item->rate }}">
                                            <input type="hidden" class="potax" name="poitem_taxrate[]" value="{{ $item->taxrate }}">
                                        </tr>
                                        @php ($i++)
                                    @endif
                                @endforeach
                                <tr>
                                    <td colspan="6"></td>
                                    <td>
                                        <b>Number of Goods</b>
                                        <input type="text" class="form-control" name="stock_grn" value="0" id="stock_grn" readonly>
                                        <input type="hidden" name="stock_subttl" value="0" id="stock_subttl">
                                        <input type="hidden" name="stock_tax" value="0" id="stock_tax">
                                        <input type="hidden" name="stock_grandttl" value="0" id="stock_grandttl">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- expense tab -->
                    <div class="tab-pane" id="active2" aria-labelledby="equipment-maintained" role="tabpanel">
                        <table class="table-responsive tfr" width="100%" id="expTbl">
                            <thead>
                                <tr class="item_header bg-gradient-directional-danger white">
                                    <th width="6%" class="text-center">#</th>
                                    <th width="35%" class="text-center">Product Description</th>
                                    <th width="10%" class="text-center">PO Qty</th>
                                    <th width="10%">Qty Received</th>
                                    <th width="10%" class="text-center">Qty</th>
                                    <th width="10%" class="text-center">DNote</th>                            
                                    <th width="16%" class="text-center">Date</th>
                                </tr>                                
                            </thead>
                            <tbody>
                                @php ($i = 1)
                                @foreach ($po->products as $item)
                                    @if ($item->type == 'Expense')
                                        <tr>
                                            <td class="text-center">{{$i}}</td>
                                            <td><textarea name="description" cols="50" rows="3" disabled>{{ $item->description }}</textarea></td>
                                            <td><input type="text" class="form-control" value="{{ (float) $item->qty }}" disabled></td>
                                            <td><input type="text" class="form-control" name="grn_qty[]" value="{{ $item->grn_items->sum('qty') }}" readonly></td>
                                            <td><input type="number" step=".01" class="form-control qty" name="qty[]"></td>
                                            <td><input type="text" class="form-control" name="dnote[]"></td>
                                            <td><input type="text" class="form-control datepicker" name="date[]"></td>
                                            <input type="hidden" name="poitem_id[]" value="{{ $item->id }}">
                                            <input type="hidden" class="porate" name="poitem_rate[]" value="{{ $item->rate }}">
                                            <input type="hidden" class="potax" name="poitem_taxrate[]" value="{{ $item->taxrate }}">
                                        </tr>
                                        @php ($i++)
                                    @endif
                                @endforeach
                                <tr>
                                    <td colspan="6"></td>
                                    <td>
                                        <b>Number of Goods</b>
                                        <input type="text" class="form-control" name="expense_grn" value="0" id="expense_grn" readonly>
                                        <input type="hidden" name="expense_subttl" value="0" id="expense_subttl">
                                        <input type="hidden" name="expense_tax" value="0" id="expense_tax">
                                        <input type="hidden" name="expense_grandttl" value="0" id="expense_grandttl">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- asset tab -->
                    <div class="tab-pane" id="active3" aria-labelledby="equipment-maintained" role="tabpanel">
                        <table class="table-responsive tfr" width="100%" id="assetTbl">
                            <thead>
                                <tr class="item_header bg-gradient-directional-success white">
                                    <th width="6%" class="text-center">#</th>
                                    <th width="35%" class="text-center">Product Description</th>
                                    <th width="10%" class="text-center">PO Qty</th>
                                    <th width="10%">Qty Received</th>
                                    <th width="10%" class="text-center">Qty</th>
                                    <th width="10%" class="text-center">DNote</th>                            
                                    <th width="16%" class="text-center">Date</th>
                                </tr>                                
                            </thead>
                            <tbody>
                                @php ($i = 1)
                                @foreach ($po->products as $item)
                                    @if ($item->type == 'Asset')                                    
                                        <tr>
                                            <td class="text-center">{{$i}}</td>
                                            <td><textarea name="description" cols="50" rows="3" disabled>{{ $item->description }}</textarea></td>
                                            <td><input type="text" class="form-control" value="{{ (float) $item->qty }}" disabled></td>
                                            <td><input type="text" class="form-control" name="grn_qty[]" value="{{ $item->grn_items->sum('qty') }}" readonly></td>
                                            <td><input type="number" step=".01" class="form-control qty" name="qty[]"></td>
                                            <td><input type="text" class="form-control" name="dnote[]"></td>
                                            <td><input type="text" class="form-control datepicker" name="date[]"></td>
                                            <input type="hidden" name="poitem_id[]" value="{{ $item->id }}">
                                            <input type="hidden" class="porate" name="poitem_rate[]" value="{{ (float) $item->rate }}">
                                            <input type="hidden" class="potax" name="poitem_taxrate[]" value="{{ (float) $item->taxrate }}">
                                        </tr>
                                        @php ($i++)
                                    @endif
                                @endforeach
                                <tr>
                                    <td colspan="6"></td>
                                    <td>
                                        <b>Number of Goods</b>
                                        <input type="text" class="form-control" name="asset_grn" value="0" id="asset_grn" readonly>
                                        <input type="hidden" name="asset_tax" value="0" id="asset_tax">
                                        <input type="hidden" name="asset_subttl" value="0" id="asset_subttl">
                                        <input type="hidden" name="asset_grandttl" value="0" id="asset_grandttl">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            {{ Form::close() }}
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
<script>
    // datepicker
    $('.datepicker')
    .datepicker({ format: "{{config('core.user_date_format')}}"})    
    .datepicker('setDate', new Date())
    .change(function() { $(this).datepicker('hide') });

    // Quantity on change
    $('#stockTbl').on('change', '.qty', function() {
        calcTotal('stockTbl');
    });
    $('#expTbl').on('change', '.qty', function() {
        calcTotal('expTbl');
    });
    $('#assetTbl').on('change', '.qty', function() {
        calcTotal('assetTbl');
    });

    // calculate totals
    function calcTotal(id) {
        let stockSubttl = 0;
        let stockTax = 0;
        let stockGrn = 0;
        let expSubttl = 0;
        let expTax = 0;
        let expGrn = 0;
        let assetSubttl = 0;
        let assetTax = 0;
        let assetGrn = 0;
        $('#'+id+' tbody tr').each(function() {
            const qty = $(this).find('.qty').val() * 1;
            if (qty) {
                const poRate = $(this).find('.porate').val();
                const poTax = $(this).find('.potax').val();
                switch(id) {
                    case 'stockTbl':
                        stockSubttl += qty * poRate;
                        stockTax += qty * poTax;
                        stockGrn += qty*1;
                        break;
                    case 'expTbl':
                        expSubttl += qty * poRate;
                        expTax += qty * poTax;
                        expGrn += qty*1;
                        break;
                    case 'assetTbl':
                        assetSubttl += qty * poRate;
                        assetTax += qty * poTax;
                        assetGrn += qty*1;
                        break;
                }
            }
        });

        switch(id) {
            case 'stockTbl':
                $('#stock_grn').val(stockGrn);
                $('#stock_subttl').val(stockSubttl);
                $('#stock_tax').val(stockTax);
                $('#stock_grandttl').val(stockSubttl+stockTax);
                break;
            case 'expTbl':
                $('#expense_grn').val(expGrn);
                $('#expense_subttl').val(expSubttl);
                $('#expense_tax').val(expTax);
                $('#expense_grandttl').val(expSubttl+expTax);
                break;
            case 'assetTbl':
                $('#asset_grn').val(assetGrn);
                $('#asset_subttl').val(assetSubttl);
                $('#asset_tax').val(assetTax);
                $('#asset_grandttl').val(assetSubttl+assetTax);
                break;
        }
        $('#grandtax').val(stockTax + expTax + assetTax);
        $('#paidttl').val(stockSubttl + expSubttl + assetSubttl);
        const grandTtl = $('#grandtax').val()*1 + $('#paidttl').val()*1;
        $('#grandttl').val(grandTtl);
    }
</script>
@endsection
