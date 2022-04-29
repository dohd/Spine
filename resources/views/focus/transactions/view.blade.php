@extends ('core.layouts.app')

@section ('title', 'Transactions Management')

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
            <div class="card-body">
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
                        trans('transactions.account_id') => $tr->account['holder'],
                        trans('transactions.trans_category_id') => $tr->category['name'],
                        'Transaction Type' => $tr_types[$tr->tr_type],
                        trans('transactions.debit') => amountFormat($tr['debit']),
                        trans('transactions.credit') => amountFormat($tr['credit']),
                        trans('transactions.payment_date') => dateFormat($tr['tr_date']),
                        trans('general.employee') => $tr->user['first_name'].' '.$tr->user['last_name'],
                        trans('general.note') => $tr['note'],                                    
                    ];
                @endphp
                <div class="mr-1 ml-1">
                    @foreach ($tr_details as $key => $value)
                        <div class="row">
                            <div class="col-3 border-blue-grey border-lighten-5  font-weight-bold p-1">
                                <p>{{ $key }}</p>
                            </div>
                            <div class="col border-blue-grey border-lighten-5  p-1">
                                <p>
                                    @if ($key == 'Transaction Type')                                                
                                        <a href="{{ $tr_type_urls[$value] }}">{{ $value }}</a>
                                    @else
                                        {{ $value }} &nbsp;&nbsp;
                                        @if ($key == trans('transactions.trans_category_id'))
                                            <a href="{{ route('biller.print_payslip', [$transaction['id'], 1, 1]) }}" class="btn btn-blue round">
                                                <span class="fa fa-print" aria-hidden="true"></span>
                                            </a>
                                        @endif
                                    @endif                                
                                </p>
                            </div>
                        </div>
                    @endforeach 
                </div>
            </div>
        </div>
    </div>
</div>
@endsection