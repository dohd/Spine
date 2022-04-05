@extends ('core.layouts.app')

@section ('title', 'Direct Purchase Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-1">
            <h4>Direct Purchase Management</h4>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.purchases.partials.purchases-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">            
            <ul class="nav nav-tabs nav-top-border no-hover-bg nav-justified" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="active-tab1" data-toggle="tab" href="#active1" aria-controls="active1" role="tab" aria-selected="true">
                        Direct Purchase Details
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
                            @php
                                $purchase_details = [
                                    'Supplier' => $purchase->suppliername,
                                    'Tax ID' => $purchase->supplier_taxid,
                                    'Transaction ID' => $purchase->tid,
                                    'Order Date & Due Date' => $purchase->date . ' : ' . $purchase->due_date,
                                    'Reference' => $purchase->doc_ref_type . ' - ' . $purchase->doc_ref,
                                    'Project' => $purchase->project ?: $purchase->project->name,
                                    'Note' => $purchase->note,
                                ];
                            @endphp   
                            @foreach ($purchase_details as $key => $val)
                                <tr>
                                    <th>{{ $key }}</th>
                                    <td>{{ $value }}</td>
                                </tr>
                            @endforeach                      
                            <tr>
                                <th>Purchase Items Cost</th>
                                <td>
                                    <b>Stock:</b>   {{ amountFormat($purchase->stock_grandttl) }}<br>
                                    <b>Expense:</b> {{ amountFormat($purchase->expense_grandttl) }}<br>
                                    <b>Asset:</b> {{ amountFormat($purchase->asset_grandttl) }}<br>
                                    <b>Total:</b> {{ amountFormat($purchase->grandttl) }}<br>
                                </td>
                            </tr>                              
                        </tbody>
                    </table>
                </div>

                <div class="tab-pane" id="active2" aria-labelledby="equipment-maintained" role="tabpanel">
                    <table class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                        <tr>
                            <th>Product Description</th>
                            <th>Quantity</th>
                            <th>Rate</th>
                            <th>Tax</th>
                            <th>Tax Rate</th>
                            <th>Amount</th>
                        </tr>
                        <tbody>
                            @foreach ($purchase->products as $item)
                                @if ($item->type == 'Stock')
                                    <tr>
                                        <td>{{ $item->description }}</td>
                                        <td>{{ (int) $item->qty }}</td>
                                        <td>{{ number_format($item->rate, 2) }}</td>
                                        <td>{{ (int) $item->itemtax }}%</td>
                                        <td>{{ number_format($item->taxrate, 2) }}</td>
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
                            <th>Rate</th>
                            <th>Tax</th>
                            <th>Tax Rate</th>                            
                            <th>Amount</th>
                        </tr>
                        <tbody>
                            @foreach ($purchase->products as $item)
                                @if ($item->type == 'Expense')
                                    <tr>
                                        <td>{{ $item->description }}</td>
                                        <td>{{ (int) $item->qty }}</td>
                                        <td>{{ number_format($item->rate, 2) }}</td>
                                        <td>{{ (int) $item->itemtax }}%</td>
                                        <td>{{ number_format($item->taxrate, 2) }}</td>
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
                            <th>Rate</th>
                            <th>Tax</th>
                            <th>Tax Rate</th>                            
                            <th>Amount</th>
                        </tr>
                        <tbody>
                            @foreach ($purchase->products as $item)
                                @if ($item->type == 'Asset')
                                    <tr>
                                        <td>{{ $item->description }}</td>
                                        <td>{{ (int) $item->qty }}</td>
                                        <td>{{ number_format($item->rate, 2) }}</td>
                                        <td>{{ (int) $item->itemtax }}%</td>
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