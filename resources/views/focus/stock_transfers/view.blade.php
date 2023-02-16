@extends ('core.layouts.app')

@section('title', 'View | Stock Transfer Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Stock Transfer Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.stock_transfers.partials.stock-transfer-header-buttons')
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
                                'Transfer No' => gen4tid('STR-', $stock_transfer->tid),
                                'Destination Location' => $stock_transfer->destination_location? $stock_transfer->destination_location->title : '',
                                'Source Location' => $stock_transfer->source_location? $stock_transfer->source_location->title : '',
                                'Stock Worth' => numberFormat($stock_transfer->total),
                                'Note' => $stock_transfer->note,
                            ];
                        @endphp
                        @foreach ($details as $key => $val)
                            <tr>
                                <th width="30%">{{ $key }}</th>
                                <td>
                                    @if ($key == 'Stock Transfer Status')
                                        <span class="text-success">{{ $val }}</span>
                                    @else
                                        {{ $val }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </table>

                    <div class="table-responsive">
                        <table class="table tfr my_stripe_single text-center" id="productsTbl">
                            <thead>
                                <tr class="bg-gradient-directional-blue white">
                                    <th width="5%">#</th>
                                    <th width="25%">Item Description</th>
                                    <th width="10%">Stock Qty</th>
                                    <th width="10%">Qty Transfered</th>
                                    <th>UoM</th>
                                    <th>Unit Price</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($stock_transfer->items as $i => $item)
                                    @php
                                        $prodvariation = $item->product_variation;
                                        if (!$prodvariation) continue;
                                    @endphp
                                    <tr>
                                        <td>{{ $i+1 }}</td>
                                        <td>{{ $prodvariation->name }}</td>
                                        <td>{{ +$prodvariation->qty }}</td>
                                        <td>{{ +$item->qty }}</td>
                                        <td>{{ $item->uom }}</td>
                                        <td>{{ numberFormat($item->unit_price) }}</td>
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
