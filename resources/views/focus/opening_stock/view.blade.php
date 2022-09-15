@extends ('core.layouts.app')

@section('title', 'Product Opening Stock')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Product Opening Stock</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.opening_stock.partials.opening-stock-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        @php
                            $details = [
                                'Transaction No' => $opening_stock->tid,
                                'Date' => dateFormat($opening_stock->date),
                                'Note' => $opening_stock->note,
                                'Warehouse' => $opening_stock->warehouse->title,
                                'Amount' => numberFormat($opening_stock->total)
                            ];
                        @endphp
                        @foreach ($details as $key => $val)
                            <tr>
                                <th width="30%">{{ $key }}</th>
                                <td>{{ $val }}</td>
                            </tr>
                        @endforeach
                    </table>

                    <div class="table-responsive">
                        <table class="table table-sm tfr my_stripe_single" id="invoiceTbl">
                            <thead>
                                <tr class="bg-gradient-directional-blue white">
                                    <th>#</th>
                                    <th>Description</th>
                                    <th width="10%">Base Unit</th>
                                    <th width="16%">Purchase Price</th>
                                    <th width="12%">Unit Qty</th>
                                    <th width="16%">Amount</th>
                                </tr>
                            </thead>
                            <tbody>   
                                @foreach ($opening_stock->items as $i => $item)
                                    <tr>
                                        <td>{{ $i+1 }}</td>
                                        <td>{{ $item->productvariation->name }}</td>
                                        <td>{{ $item->product->unit? $item->product->unit->code : ''}}</td>
                                        <td>{{ numberFormat($item->purchase_price) }}</td>
                                        <td>{{ +$item->qty }}</td>
                                        <td>{{ numberFormat($item->amount) }}</td>
                                    </tr>
                                @endforeach                                
                            </tbody>                
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
