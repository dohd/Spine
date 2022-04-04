@extends ('core.layouts.app')

@section ('title', trans('labels.backend.transactions.management') . ' | ' . trans('labels.backend.transactions.create'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h3 class="content-header-title mb-0">{{ trans('labels.backend.transactions.view') }}</h3>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.transactions.partials.transactions-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            @php
                                $tr = $transaction;
                                $tr_types = ['BILL' => 'Bill', 'PMT' => 'Payment', 'INV' => 'Invoice'];
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
                            @foreach ($tr_details as $key => $value)
                                <div class="row">
                                    <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                        <p>{{ $key }}</p>
                                    </div>
                                    <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                        <p>
                                            @if ($key == 'Transaction Type')
                                                <a href="#">{{ $value }}</a>
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
    </div>
</div>
@endsection
