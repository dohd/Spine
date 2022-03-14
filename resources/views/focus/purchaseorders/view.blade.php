@extends ('core.layouts.app')

@section ('title', 'Purchase Order Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-1">
            <h4>Purchase Order Management</h4>
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
        <h5 class="card-header">
            @php
                //$valid_token = token_validator('', 'd' . $djc->id, true);
                //$link = route('biller.print_djc', [$djc->id, 10, $valid_token, 1]);
            @endphp
            <a href="{{ '' }}" class="btn btn-purple">
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
                <div class="tab-pane active in" id="active1" aria-labelledby="customer-details" role="tabpanel">
                    <table id="customer-table" class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                        <tbody>                            
                            <tr>
                                <th>Supplier</th>
                                <td>{{ $po->suppliername }}</td>
                            </tr>
                            <tr>
                                <th>Tax ID</th>
                                <td>{{ $po->supplier_taxid }}</td>
                            </tr>
                            <tr>
                                <th>Transaction ID</th>
                                <td>{{ $po->transxn_ref }}</td>
                            </tr>
                            <tr>
                                <th>Order Date</th>
                                <td>{{ dateFormat($po->date) }}</td>
                            </tr>
                            <tr>
                                <th>Order Due Date</th>
                                <td>{{ dateFormat($po->due_date) }}</td>
                            </tr>
                            <tr>
                                <th>Reference No.</th>
                                <td>{{ $po->doc_ref }}</td>
                            </tr>
                            <tr>
                                <th>Project</th>
                                <td>{{ $po->project ? $po->project->name : '' }}</td>
                            </tr>
                            <tr>
                                <th>Note</th>
                                <td>{{ $po->note }}</td>
                            </tr>                             
                        </tbody>
                    </table>
                </div>

                <div class="tab-pane" id="active2" aria-labelledby="equipment-maintained" role="tabpanel">
                    <table class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                        <tr>
                            <th>Product Description</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Tax Rate</th>
                            <th>Tax</th>
                            <th>Amount</th>
                        </tr>
                        <tbody>
                            @foreach ($po->items as $item)
                                @if ($item->type == 'Stock')
                                    <tr>
                                        <td>{{ $item->description }}</td>
                                        <td>{{ (int) $item->qty }}</td>
                                        <td>{{ number_format($item->rate, 2) }}</td>
                                        <td>{{ (int) $item->tax_rate }}%</td>
                                        <td>{{ number_format($item->tax, 2) }}</td>
                                        <td>{{ number_format($item->amount, 2) }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="tab-pane" id="active3" aria-labelledby="other-details" role="tabpanel">
                    <table class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                        <tr>
                            <th>Product Description</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Tax Rate</th>
                            <th>Tax</th>
                            <th>Amount</th>
                        </tr>
                        <tbody>
                            @foreach ($po->items as $item)
                                @if ($item->type == 'Expense')
                                    <tr>
                                        <td>{{ $item->description }}</td>
                                        <td>{{ (int) $item->qty }}</td>
                                        <td>{{ number_format($item->rate, 2) }}</td>
                                        <td>{{ (int) $item->tax_rate }}%</td>
                                        <td>{{ number_format($item->tax, 2) }}</td>
                                        <td>{{ number_format($item->amount, 2) }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="tab-pane" id="active4" aria-labelledby="other-details" role="tabpanel">
                    <table class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                        <tr>
                            <th>Product Description</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Tax Rate</th>
                            <th>Tax</th>
                            <th>Amount</th>
                        </tr>
                        <tbody>
                            @foreach ($po->items as $item)
                                @if ($item->type == 'Asset')
                                    <tr>
                                        <td>{{ $item->description }}</td>
                                        <td>{{ (int) $item->qty }}</td>
                                        <td>{{ number_format($item->rate, 2) }}</td>
                                        <td>{{ (int) $item->tax_rate }}%</td>
                                        <td>{{ number_format($item->tax, 2) }}</td>
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