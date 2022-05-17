@extends ('core.layouts.app')

@section ('title', 'WithHolding Tax management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">WithHolding Tax  Management</h4>
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
                        'Transaction ID' => $withholding->tid,
                        'Customer' => $withholding->customer->company,
                        'Date' => dateFormat($withholding->date),
                        'Due Date' => dateFormat($withholding->due_date),
                        'Amount' => numberFormat($withholding->deposit_ttl),
                        'Certificate' => strtoupper($withholding->certificate),
                        'Reference' => $withholding->doc_ref
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
            <h4><b>Invoices</b></h4>
            <table class="table table-sm text-center">
                <thead>
                    <tr class="bg-gradient-directional-blue white">
                        <th>Date</th>
                        <th>Note</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($withholding->items as $item)
                        <tr>
                            <td>{{ dateFormat($item->invoice->invoicedate) }}</td>
                            <td>{{ $item->invoice->notes }}</td>
                            <td>{{ numberFormat($item->paid) }}</td>
                        </tr>
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