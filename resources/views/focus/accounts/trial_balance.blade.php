@extends ('core.layouts.app')
@section ('title', 'Trial Balance | ' . trans('labels.backend.accounts.management'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h3>
                Trial Balance
                <a class="btn btn-success btn-sm" href="{{ route('biller.accounts.balance_sheet', 'p') }}">
                    <i class="fa fa-print"></i> {{ trans('general.print') }}
                </a>
            </h3>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.accounts.partials.accounts-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content print_me">
                        <div class="title bg-gradient-x-info p-1 white"></div>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Account No</th>
                                    <th>{{trans('accounts.account')}}</th>
                                    <th>Debit ({{config('currency.symbol')}})</th>
                                    <th>Credit ({{config('currency.symbol')}})</th>
                                </tr>
                            </thead>
                            <tbody>   
                                @php
                                    $debit_total = 0;
                                    $credit_total = 0;
                                @endphp
                                @foreach ($accounts as $i => $account)
                                    @php
                                        $debit = $account->transactions->sum('debit');
                                        $credit = $account->transactions->sum('credit');
                                        $debit_total += $debit;
                                        $credit_total += $credit;
                                    @endphp
                                    <tr>
                                        <td>{{ $i+1 }}</td>
                                        <td>{{ $account->number }}</td>
                                        <td>{{ $account->holder }}</td>
                                        <td>{{ numberFormat($debit) }}</td>
                                        <td>{{ numberFormat($credit) }}</td>
                                    </tr> 
                                @endforeach
                                <tr>
                                    @for ($i = 0; $i < 3; $i++)
                                        <td></td>
                                    @endfor 
                                    @foreach ([$debit_total, $credit_total] as $val)
                                        <td><h3 class="text-xl-left">{{ amountFormat($val) }}</h3></td>
                                    @endforeach                                       
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection