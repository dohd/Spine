@extends ('core.layouts.app')

@section ('title', 'Purchase Order Management')

@section('content')
@php $po = $purchaseorder; @endphp
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
    
    <div class="card">
        <div class="card-header">
            <a href="{{ route('biller.print_purchaseorder', [$purchaseorder->id, 9, token_validator('', 'po' . $purchaseorder->id, true), 1]) }}" class="btn btn-purple btn-sm" target="_blank">
                <i class="fa fa-print" aria-hidden="true"></i> Print
            </a>
            &nbsp;
            <a href="#" class="btn btn-danger btn-sm mr-1" data-toggle="modal" data-target="#statusModal">
                <i class="fa fa-times" aria-hidden="true"></i> Close Order
            </a>
        </div>            
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
                    @if ($po->closure_status)
                        <div class="badge text-center white d-block m-1">
                            <span class="bg-danger round p-1"><b>Purchase Order Closed</b></span>
                        </div>
                        <h6 class="text-center">
                            {{ $po->closure_reason }}
                        </h6>
                    @endif  
                    <br>
                    <table id="customer-table" class="table table-sm table-bordered zero-configuration" cellspacing="0" width="100%">
                        <tbody>  
                            @php   
                                $details = [
                                    'Order NO' => gen4tid('PO-', $po->tid),
                                    'Supplier' => @$po->supplier->name,
                                    'Date' => dateFormat($po->date),
                                    'Due Date' => dateFormat($po->due_date),
                                    'Document' => $po->doc_ref_type && $po->doc_ref? "{$po->doc_ref_type} - {$po->doc_ref}" : '',
                                    'Project' => $po->project ? gen4tid('Prj-', $po->project->tid) . '; ' . $po->project->name : '',
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
                                        <td>{{ number_format($item->qty, 1) }}</td>
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
                            <th>UoM</th>
                            <th>Price</th>
                            <th>Tax</th>
                            <th>Amount</th>
                            <th>Ledger Account</th>
                            <th>Project</th>
                        </tr>
                        <tbody>
                            @foreach ($po->products as $item)
                                @if ($item->type == 'Expense')
                                    <tr>
                                        <td>{{ $item->description }}</td>
                                        <td>{{ number_format($item->qty, 1) }}</td>
                                        <td>{{ $item->uom }}</td>
                                        <td>{{ numberFormat($item->rate) }}</td>
                                        <td>{{ numberFormat($item->taxrate) }}</td>
                                        <td>{{ numberFormat($item->amount) }}</td>
                                        <td>{{ $item->account? $item->account->holder : '' }}</td>
                                        <td>
                                            @isset($item->project->tid)
                                                {{ gen4tid('Prj-', $item->project->tid) }}; {{ $item->project->name }}
                                            @endisset
                                        </td>
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
                            <th>UoM</th>
                            <th>Price</th>
                            <th>Tax</th>
                            <th>Amount</th>
                        </tr>
                        <tbody>
                            @foreach ($po->products as $item)
                                @if ($item->type == 'Asset')
                                    <tr>
                                        <td>{{ $item->description }}</td>
                                        <td>{{ number_format($item->qty, 1) }}</td>
                                        <td>{{ $item->uom }}</td>
                                        <td>{{ numberFormat($item->rate) }}</td>
                                        <td>{{ numberFormat($item->taxrate) }}</td>
                                        <td>{{ numberFormat($item->amount) }}</td>
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
@include('focus.purchaseorders.partials.status_modal')
@endsection