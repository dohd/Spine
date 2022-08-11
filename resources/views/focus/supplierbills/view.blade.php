@extends ('core.layouts.app')

@section('title', 'Supplier Bill Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Supplier Bill Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.supplierbills.partials.supplierbills-header-buttons')
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
                                'Bill No' => gen4tid('BILL-', $supplierbill->tid),
                                'Supplier' => $supplierbill->supplier? $supplierbill->supplier->name : '', 
                                'Date' => dateFormat($supplierbill->date),
                                'Due Date' => dateFormat($supplierbill->due_date),
                                'Subtotal' => numberFormat($supplierbill->subtotal),
                                'Tax' => numberFormat($supplierbill->tax),
                                'Total' => numberFormat($supplierbill->total),
                                'Amount Paid' => numberFormat($supplierbill->amountpaid),
                                'Balance' => numberFormat($supplierbill->total - $supplierbill->amountpaid),
                                'Note' => $supplierbill->note,
                            ];
                        @endphp
                        @foreach ($details as $key => $val)
                            <tr>
                                <th width="30%">{{ $key }}</th>
                                <td>{{ $val }}</td>
                            </tr>
                        @endforeach
                    </table>
                    {{-- goods receive note --}}
                    <div class="table-responsive mt-3">
                        <table class="table tfr my_stripe_single text-center" id="invoiceTbl">
                            <thead>
                                <tr class="bg-gradient-directional-blue white">
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>GRN No.</th>
                                    <th>Purchase Type</th>
                                    <th>Dnote</th>
                                    <th>Note</th>
                                    <th>Rate</th>                                         
                                </tr>
                            </thead>
                            <tbody>   
                                @foreach ($supplierbill->items as $i => $item)
                                    @if ($item->grn)
                                        <tr>
                                            <td>{{ $i+1 }}</td>
                                            <td>{{ dateFormat($item->grn->date) }}</td>
                                            <td>{{ gen4tid('GRN-', $item->grn->tid) }}</td>
                                            <td>{{ $item->grn->purchaseorder? gen4tid('PO-', $item->grn->purchaseorder->tid) . ' - ' . $item->grn->purchaseorder->note : '' }}</td>
                                            <td>{{ $item->grn->dnote }}</td>
                                            <td>{{ $item->grn->note }}</td>
                                            <td>{{ numberFormat($item->grn->total) }}</td>                                        
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
@endsection
