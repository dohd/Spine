@extends ('core.layouts.app')

@section('title', 'Invoice Payment Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Invoice Payment Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.invoice_payments.partials.invoice-payment-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <table id="payment-table" class="table table-sm table-bordered zero-configuration" cellspacing="0" width="100%">
                        <tbody>   
                            @php
                                $pmt = $invoice_payment;
                                $payment_details = [
                                    'Payment No' => $pmt->tid,
                                    'Customer' => @$pmt->customer->company,
                                    'Date' => dateFormat($pmt->date),
                                    'Amount' => numberFormat($pmt->amount),
                                    'Allocated Amount' => numberFormat($pmt->allocate_ttl),
                                    'Payment Mode' => $pmt->payment_mode,
                                    'Reference' => $pmt->reference,
                                    'Payment Account' => @$pmt->account->holder,
                                    'Payment Type' =>  ucfirst(str_replace('_', ' ', $pmt->payment_type)),
                                ];
                            @endphp   
                            @foreach ($payment_details as $key => $val)
                                <tr>
                                    <th>{{ $key }}</th>
                                    <td>{{ $val }}</td>
                                </tr>
                            @endforeach                           
                        </tbody>
                    </table>
                    <!-- invoices -->
                    <div class="table-responsive">
                        <table class="table tfr my_stripe_single text-center" id="invoiceTbl">
                            <thead>
                                <tr class="bg-gradient-directional-blue white">
                                    <th>#</th>
                                    <th>Due Date</th>
                                    <th>Invoice No</th>
                                    <th>Note</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                    <th>Paid</th>
                                    <th>Balance</th>                                    
                                </tr>
                            </thead>
                            <tbody>   
                                @foreach ($invoice_payment->items as $i => $item)
                                    @if ($item->invoice)
                                        <tr>
                                            <td>{{ $i+1 }}</td>
                                            <td>{{ dateFormat($item->invoice->invoiceduedate) }}</td>
                                            <td>{{ gen4tid('Inv-', $item->invoice->tid) }}</td>
                                            <td>{{ $item->invoice->notes }}</td>
                                            <td>{{ $item->invoice->status }}</td>
                                            <td>{{ numberFormat($item->invoice->total) }}</td>
                                            <td>{{ numberFormat($item->invoice->amountpaid) }}</td>
                                            <td>{{ numberFormat($item->invoice->total - $item->invoice->amountpaid) }}</td>                                            
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
