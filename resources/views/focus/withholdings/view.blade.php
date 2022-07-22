@extends ('core.layouts.app')

@section ('title', 'WithHolding Tax management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">WithHolding Tax Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.withholdings.partials.withholdings-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <table id="withholdingTbl" class="table table-sm table-bordered mb-2">
                @php
                    $details = [
                        'TID' => gen4tid('WH-', $withholding->tid),
                        'Customer' => $withholding->customer->company,
                        'Certificate Date' => dateFormat($withholding->cert_date),
                        'Payment / Transaction Date' => dateFormat($withholding->tr_date),
                        'Amount' => numberFormat($withholding->amount),
                        'Certificate' => strtoupper($withholding->certificate),
                        'Certificate Serial No' => $withholding->reference,
                        'Allocated' => numberFormat($withholding->allocate_ttl),
                    ];
                @endphp
                <tbody>                    
                    @foreach ($details as $key => $val)
                        <tr>
                            <th>{{ $key }}</th>
                            <td>{{ $val }}</td>
                        </tr> 
                    @endforeach                    
                </tbody>
            </table>
            <table class="table table-sm text-center">
                <thead>
                    <tr class="bg-gradient-directional-blue white">
                        <th>Date</th>
                        <th>Invoice No</th>
                        <th>Note</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($withholding->items as $item)
                        @if ($item->invoice)
                            <tr>
                                <td>{{ dateFormat($item->invoice->invoicedate) }}</td>
                                <td>{{ $item->invoice->tid }}</td>
                                <td>{{ $item->invoice->notes }}</td>
                                <td>{{ numberFormat($item->paid) }}</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
<script>
    $('.datepicker')
    .datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    .datepicker('setDate', new Date());
</script>
@endsection