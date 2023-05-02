@extends ('core.layouts.app')

@section ('title', 'Direct Purchase Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4>Direct Purchase Management</h4>
        </div>
        <div class="content-header-right col-6">
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
                <!-- Purchase details -->
                <div class="tab-pane active in" id="active1" aria-labelledby="customer-details" role="tabpanel">
                    <table id="customer-table" class="table table-sm table-bordered zero-configuration" cellspacing="0" width="100%">
                        <tbody>   
                            @php
                                $project = $purchase->project ? gen4tid('Prj-', $purchase->project->tid) . '; ' . $purchase->project->name : '';
                                $purchase_details = [
                                    'System ID' => gen4tid('DP-', $purchase->tid),
                                    'Supplier' => ($purchase->suppliername? $purchase->suppliername : $purchase->supplier)? $purchase->supplier->name : '',
                                    'Tax ID' => $purchase->supplier_taxid,
                                    'Order Date & Due Date' => $purchase->date . ' : ' . $purchase->due_date,
                                    'Reference' => $purchase->doc_ref_type . ' - ' . $purchase->doc_ref,
                                    'Project' => $project,
                                    'Note' => $purchase->note,
                                ];
                            @endphp   
                            @foreach ($purchase_details as $key => $val)
                                <tr>
                                    <th>{{ $key }}</th>
                                    <td>{{ $val }}</td>
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

                <!-- Inventory / stock -->
                <div class="tab-pane" id="active2" aria-labelledby="equipment-maintained" role="tabpanel">
                    <table class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                        <tr>
                            <th>Product Description</th>
                            <th>Quantity</th>
                            <th>Rate</th>
                            <th>Tax</th>
                            <th>Tax Rate</th>
                            <th>Amount ({{ $purchase->is_tax_exc? 'VAT Exc' : 'VAT Inc' }})</th>
                            <th>Project</th>
                        </tr>
                        <tbody>
                            @foreach ($purchase->products as $item)
                                @if ($item->type == 'Stock')
                                    <tr>
                                        <td>{{ $item->description }}</td>
                                        <td>{{ number_format($item->qty, 1) }}</td>
                                        <td>{{ numberFormat($item->rate) }}</td>
                                        <td>{{ (int) $item->itemtax }}%</td>
                                        <td>{{ numberFormat($item->taxrate) }}</td>
                                        <td>{{ numberFormat($item->amount) }}</td>
                                        <td>
                                            @if($item->project)
                                                {{ gen4tid('Prj-', $item->project->tid) }} - {{ $item->project->name }}
                                            @endif
                                        </td>
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
                            <th>Rate</th>
                            <th>Tax</th>
                            <th>Tax Rate</th>                            
                            <th>Amount ({{ $purchase->is_tax_exc? 'VAT Exc' : 'VAT Inc' }})</th>
                            <th>Ledger Account</th>
                            <th>Project</th>
                        </tr>
                        <tbody>
                            @foreach ($purchase->products as $item)
                                @if ($item->type == 'Expense')
                                    <tr>
                                        <td>{{ $item->description }}</td>
                                        <td>{{ number_format($item->qty, 1) }}</td>
                                        <td>{{ numberFormat($item->rate) }}</td>
                                        <td>{{ (int) $item->itemtax }}%</td>
                                        <td>{{ numberFormat($item->taxrate) }}</td>
                                        <td>{{ numberFormat($item->amount) }}</td>
                                        <td>{{ $item->account? $item->account->holder : '' }}</td>
                                        <td>
                                            @if($item->project)
                                                {{ gen4tid('Prj-', $item->project->tid) }} - {{ $item->project->name }}
                                            @endif
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
                            <th>Rate</th>
                            <th>Tax</th>
                            <th>Tax Rate</th>                            
                            <th>Amount ({{ $purchase->is_tax_exc? 'VAT Exc' : 'VAT Inc' }})</th>
                            <th>Ledger Account</th>
                            <th>Project</th>
                        </tr>
                        <tbody>
                            @foreach ($purchase->products as $item)
                                @if ($item->type == 'Asset')
                                    <tr>
                                        <td>{{ $item->description }}</td>
                                        <td>{{ number_format($item->qty, 1) }}</td>
                                        <td>{{ numberFormat($item->rate) }}</td>
                                        <td>{{ (int) $item->itemtax }}%</td>
                                        <td>{{ numberFormat($item->taxrate) }}</td>
                                        <td>{{ numberFormat($item->amount) }}</td>
                                        <td>{{ $item->account? $item->account->holder : '' }}</td>
                                        <td>
                                            @if($item->project)
                                                {{ gen4tid('Prj-', $item->project->tid) }} - {{ $item->project->name }}
                                            @endif
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
@endsection