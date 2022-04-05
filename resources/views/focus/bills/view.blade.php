@extends ('core.layouts.app')

@section ('title', 'Bill Management | View')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h3 class="content-header-title mb-0">Bill Management</h3>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-content">
            <div class="card-body">
                <ul class="nav nav-tabs nav-top-border no-hover-bg nav-justified" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="active-tab1" data-toggle="tab" href="#active1" aria-controls="active1" role="tab" aria-selected="true">
                            Bill Details
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " id="active-tab2" data-toggle="tab" href="#active2" aria-controls="active2" role="tab">
                            Bill Items
                        </a>
                    </li>
                </ul>

                <div class="tab-content px-1 pt-1">
                    <div class="tab-pane active in" id="active1" aria-labelledby="customer-details" role="tabpanel">
                        <table id="" class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                            <tbody> 
                                @php
                                    $bill_details = [
                                        'Bill Type' => $bill->po_id ? 'PURCHASE ORDER' : 'DIRECT PURCHASE',
                                        'Supplier' => $bill->supplier->name,
                                        'Tax ID' => $bill->supplier->taxid,
                                        'Transaction ID' => $bill->tid,
                                        'Order Date & Due Date' => $bill->date . ' : ' . $bill->due_date,
                                        'Reference' => $bill->doc_ref_type . ' - ' . $bill->doc_ref,
                                        'Note' => $bill->note,
                                    ];
                                    $bill_type_urls = [
                                        'PURCHASE ORDER' => route('biller.purchaseorders.show', $bill->po_id),
                                        'DIRECT PURCHASE' => route('biller.purchases.show', $bill->id)
                                    ];
                                @endphp   
                                @foreach ($bill_details as $key => $value)
                                    <tr>
                                        <th>{{ $key }}</th>
                                        <td>
                                            @if ($key == 'Bill Type')
                                                <a href="{{ $bill_type_urls[$value] }}">{{ $value }}</a>
                                            @else
                                                {{ $value }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach 
                                <tr>
                                    <th>Bill Items Cost</th>
                                    <td>
                                        <b>Stock/Inventory:</b>   {{ amountFormat($bill->stock_grandttl) }}<br>
                                        <b>Expense:</b> {{ amountFormat($bill->expense_grandttl) }}<br>
                                        <b>Asset/Equipment:</b> {{ amountFormat($bill->asset_grandttl) }}<br>
                                        <b>Total:</b> {{ amountFormat($bill->grandttl) }}<br>
                                    </td>
                                </tr>                               
                            </tbody>
                        </table>
                    </div>

                    <div class="tab-pane" id="active2" aria-labelledby="equipment-maintained" role="tabpanel">
                        <table class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                            <tr>
                                <th>Product Type</th>
                                <th>Description</th>
                                <th>Quantity</th>
                                <th>Rate</th>
                                <th>Tax</th>
                                <th>Tax Rate</th>
                                <th>Amount</th>
                            </tr>
                            <tbody>
                                @foreach ($bill->items as $item)
                                    <tr>
                                        <td>{{ $item->type }}</td>
                                        <td>{{ $item->description }}</td>
                                        <td>{{ number_format($item->qty, 2) }}</td>
                                        <td>{{ number_format($item->rate, 2) }}</td>
                                        <td>{{ (int) $item->itemtax }} %</td>
                                        <td>{{ number_format($item->taxrate, 2) }}</td>
                                        <td>{{ number_format($item->amount, 2) }}</td>
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
