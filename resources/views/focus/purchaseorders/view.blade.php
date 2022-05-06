@extends ('core.layouts.app')

@section ('title', 'Purchase Order Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4>Purchase Order Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.purchaseorders.partials.purchaseorders-header-buttons')
                </div>
            </div>
        </div>
    </div>
    @php
        $po = $purchaseorder;
        $valid_token = token_validator('', 'po' . $po->id, true);
        $link = route('biller.print_purchaseorder', [$po->id, 9, $valid_token, 1]);
    @endphp
    <div class="card">
        <h5 class="card-header">
            <a href="{{ route('biller.purchaseorders.create_grn', $po) }}" class="btn btn-primary btn-sm">
                <i class="fa fa-cubes"></i> Receive Goods
            </a>
            <a href="{{ $link }}" class="btn btn-purple btn-sm" target="_blank">
                <i class="fa fa-print" aria-hidden="true"></i> Print
            </a>
        </h5>
        <div class="card-body">            
            <ul class="nav nav-tabs nav-top-border no-hover-bg nav-justified" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="active-tab1" data-toggle="tab" href="#active1" aria-controls="active1" role="tab" aria-selected="true">
                        Purchase Order Details
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " id="active-tab2" data-toggle="tab" href="#active2" aria-controls="active2" role="tab">
                        Inventory / Stock
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " id="active-tab3" data-toggle="tab" href="#active3" aria-controls="active3" role="tab">
                        Expenses
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " id="active-tab4" data-toggle="tab" href="#active4" aria-controls="active4" role="tab">
                        Asset & Equipments
                    </a>
                </li>
            </ul>

            <div class="tab-content px-1 pt-1">
                <!-- PO details -->
                <div class="tab-pane active in" id="active1" aria-labelledby="customer-details" role="tabpanel">
                    <table id="customer-table" class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                        <tbody>  
                            @php   
                                $details = [
                                    'Supplier' => $po->supplier->name,
                                    'Transaction ID' => $po->tid,
                                    'Date & Due Date' => dateFormat($po->date) . ' : ' . dateFormat($po->due_date),
                                    'Reference' =>$po->doc_ref_type . ' - ' . $po->doc_ref,
                                    'Project' => $po->project ? $po->project->name : '',
                                    'Note' => $po->note,
                                ];                       
                            @endphp
                            @foreach ($details as $key => $val)
                                <tr>
                                    <th>{{ $key }}</th>
                                    <td>{{ $val }}</td>
                                </tr>
                            @endforeach                            
                            <tr>
                                <th>Order Items Cost</th>
                                <td>
                                    <b>Stock:</b>   {{ amountFormat($po->stock_grandttl) }}<br>
                                    <b>Expense:</b> {{ amountFormat($po->expense_grandttl) }}<br>
                                    <b>Asset:</b> {{ amountFormat($po->asset_grandttl) }}<br>
                                    <b>Total:</b> {{ amountFormat($po->grandttl) }}<br>
                                </td>
                            </tr>                              
                        </tbody>
                    </table>
                    <h4 class="mt-2"><b>Received Goods</b></h4>
                    <div class="table-responsive">
                        <table class="table tfr my_stripe_single text-center" cellspacing="0" width="100%">
                            <tr class="bg-gradient-directional-blue white">
                                <th>Product Type</th>
                                <th>Product Description</th>
                                <th>Quantity</th>
                                <th>DNote</th>
                                <th>Date</th>
                                
                            </tr>
                            <tbody>
                                @foreach ($grn_items as $item)
                                    <tr>
                                        <td>{{ $item->purchaseorder_item->type }}</td>
                                        <td>{{ $item->purchaseorder_item->description }}</td>
                                        <td>{{ number_format($item->qty) }}</td>
                                        <td>{{ $item->dnote }}</td>
                                        <td>{{ dateFormat($item->date) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>                 
                </div>

                <!-- Inventory/stock -->
                <div class="tab-pane" id="active2" aria-labelledby="equipment-maintained" role="tabpanel">
                    <table class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                        <tr>
                            <th>Product Description</th>
                            <th>Quantity</th>
                            <th>UoM</th>
                            <th>Price</th>
                            <th>Tax Rate</th>                            
                            <th>Amount</th>
                        </tr>
                        <tbody>
                            @foreach ($po->products as $item)
                                @if ($item->type == 'Stock')
                                    <tr>
                                        <td>{{ $item->description }}</td>
                                        <td>{{ (int) $item->qty }}</td>
                                        <td>{{ $item->uom }}</td>
                                        <td>{{ number_format($item->rate, 2) }}</td>                                        
                                        <td>{{ number_format($item->taxrate, 2) }}</td>
                                        <td>{{ number_format($item->amount, 2) }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Expense -->
                <div class="tab-pane" id="active3" aria-labelledby="other-details" role="tabpanel">
                    <table class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                        <tr>
                            <th>Product Description</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Tax</th>
                            <th>Amount</th>
                            <th>Project</th>
                        </tr>
                        <tbody>
                            @foreach ($po->products as $item)
                                @if ($item->type == 'Expense')
                                    <tr>
                                        <td>{{ $item->description }}</td>
                                        <td>{{ (int) $item->qty }}</td>
                                        <td>{{ number_format($item->rate, 2) }}</td>
                                        <td>{{ number_format($item->taxrate, 2) }}</td>
                                        <td>{{ number_format($item->amount, 2) }}</td>
                                        <td>{{ gen4tid('Prj-', $item->project->tid) }}; {{ $item->project->name }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Asset -->
                <div class="tab-pane" id="active4" aria-labelledby="other-details" role="tabpanel">
                    <table class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                        <tr>
                            <th>Product Description</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Tax</th>
                            <th>Amount</th>
                        </tr>
                        <tbody>
                            @foreach ($po->products as $item)
                                @if ($item->type == 'Asset')
                                    <tr>
                                        <td>{{ $item->description }}</td>
                                        <td>{{ (int) $item->qty }}</td>
                                        <td>{{ number_format($item->rate, 2) }}</td>
                                        <td>{{ number_format($item->taxrate, 2) }}</td>
                                        <td>{{ number_format($item->amount, 2) }}</td>
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
@endsection