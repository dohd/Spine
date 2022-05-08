@extends ('core.layouts.app')

@section ('title', 'Transactions Management')

@php
    $tr = $transaction;
    $tr_types = [
        'BILL' => 'BILL', 
        'PMT' => 'PAYMENT', 
        'INV' => 'INVOICE', 
        'loan' => 'LOAN', 
        'CHRG' => 'CHARGE',
        'stock' => 'STOCK'
    ];
    $tr_type_urls = [
        'BILL' => $tr->bill ? route('biller.bills.show', $tr->bill->id) : '#',
        'PAYMENT' => route('biller.show_transaction_payment', $tr->id),
        'INVOICE' => $tr->invoice ? route('biller.invoices.show', $tr->invoice->id) : '#',
        'LOAN' => $tr->loan ? route('biller.loans.show', $tr->loan->id) : '#',
        'CHARGE' => $tr->charge ? route('biller.charges.show', $tr->charge->id) : '#',
        'STOCK' => $tr->issuance ? route('biller.issuance.show', $tr->issuance->id) : '#',
    ];
    $tr_details = [
        'Account' => $tr->account->holder,
        'Category' => $tr->category->name,
        'Type' => $tr_types[$tr->tr_type],
        'Debit' => amountFormat($tr['debit']),
        'Credit' => amountFormat($tr['credit']),
        'Date' => dateFormat($tr['tr_date']),
        trans('general.employee') => $tr->user->first_name . ' ' . $tr->user->last_name,
        trans('general.note') => $tr->note,                                    
    ];
@endphp

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h3 class="content-header-title">Transactions Management</h3>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.transactions.partials.transactions-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-content">
            <div class="card-header">
                <a href="javascript:" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editTrModal">
                    <i class="fa fa-pencil"></i> Edit
                </a>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-sm">
                    @foreach ($tr_details as $key => $value)
                        <tr>
                            <th>{{ $key }}</th>
                            <td>
                                @if ($key == 'Type')                                                
                                    <a href="{{ $tr_type_urls[$value] }}">{{ $value }}</a>
                                @else
                                    {{ $value }} &nbsp;&nbsp;
                                    @if ($key == 'Category')
                                        <a href="{{ route('biller.print_payslip', [$transaction['id'], 1, 1]) }}" class="btn btn-blue round">
                                            <span class="fa fa-print" aria-hidden="true"></span>
                                        </a>
                                    @endif
                                @endif 
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
</div>
@include('focus.transactions.partials.edit-modal')
@endsection

@section("after-scripts")
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    $.ajaxSetup({headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }});

    // on account search
    $('#account').select2({
        dropdownParent: $('#editTrModal'),
        ajax: {
            url: "{{ route('biller.transactions.account_search') }}",
            dataType: 'json',
            quietMillis: 50,
            data: ({term}) => ({keyword: term}),
            processResults: data => {
                const results = data.map(v => ({id: v.id, text: v.holder}));
                return {results}; 
            },  
        }
    });
</script>
@endsection