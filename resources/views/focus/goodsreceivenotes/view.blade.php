@extends ('core.layouts.app')

@section('title', 'Goods Receive Note')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Goods Receive Note</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.goodsreceivenotes.partials.goodsreceivenotes-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <table class="table table-bordered table-sm">
                                @php
                                    $grn = $goodsreceivenote;
                                    $details = [ 
                                        'GRN No' => gen4tid('GRN', $grn->tid),
                                        'Supplier' => $grn->supplier? $grn->supplier->name : '',
                                        'Purchase Type' => $grn->purchaseorder? gen4tid('PO-', $grn->purchaseorder->tid) : '',
                                        'Dnote' => $grn->dnote,
                                        'Date' => dateFormat($grn->date),
                                        'Note' => $grn->note,
                                    ];
                                @endphp
                                @foreach ($details as $key => $val)
                                    <tr>
                                        <th width="30%">{{ $key }}</th>
                                        <td>{{ $val }}</td>
                                    </tr>
                                @endforeach
                            </table>
                            {{-- goods --}}
                            <div class="table-responsive mt-3">
                                <table class="table tfr my_stripe_single text-center" id="invoiceTbl">
                                    <thead>
                                        <tr class="bg-gradient-directional-blue white">
                                            <th>#</th>
                                            <th>Product Description</th>
                                            <th>UoM</th>
                                            <th>Qty Ordered</th>
                                            <th>Qty Received</th>
                                            <th>Qty Due</th>                                            
                                        </tr>
                                    </thead>
                                    <tbody>   
                                        @foreach ($grn->items as $i => $item)
                                            @if ($po_item = $item->purchaseorder_item)
                                                <tr>
                                                    <td>{{ $i+1 }}</td>
                                                    <td>{{ $po_item->description }}</td>
                                                    <td>{{ $po_item->uom }}</td>
                                                    <td>{{ +$po_item->qty }}</td>
                                                    <td>{{ +$po_item->qty_received }}</td>
                                                    <td>
                                                        @php
                                                            $due = $po_item->qty - $po_item->qty_received;
                                                        @endphp
                                                        {{ $due > 0? +$due : 0 }}
                                                    </td>                                                                                            
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>                
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection