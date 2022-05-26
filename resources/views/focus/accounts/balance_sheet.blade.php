@extends ('core.layouts.app')
@section ('title', trans('accounts.balance_sheet').' | ' . trans('labels.backend.accounts.management'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h3> 
                {{trans('accounts.balance_sheet')}} 
                <a class="btn btn-success btn-sm" href="{{ route('biller.accounts.balance_sheet', 'p') }}" target="_blank">
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
                @php
                    $balance_cluster = array();
                @endphp
                @foreach(['Asset', 'Equity', 'Liability', 'Summary'] as $i => $type)
                    <div class="card">
                        <div class="card-content print_me">
                            @if ($i < 3)
                                <h5 class="title {{ $bg_styles[$i] }} p-1 white">{{ $type }} {{trans('accounts.accounts')}}</h5>
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Account No</th>
                                            <th>Account</th>
                                            <th>Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $gross_balance = 0;
                                            $j = 0;
                                        @endphp
                                        @foreach ($accounts as $account)
                                            @if ($account->account_type == $type)
                                                @php
                                                    $balance = 0;
                                                    $debit = $account->transactions->sum('debit');
                                                    $credit = $account->transactions->sum('credit');
                                                    if ($type == 'Asset') $balance = $debit - $credit;
                                                    elseif ($type == 'Liability') $balance = $credit - $debit;
                                                    else $balance = $credit;
                                                    $gross_balance += $balance;
                                                    $j++;
                                                @endphp
                                                @if ($balance && $i == 1)
                                                    <!-- Equity -->
                                                    <tr>
                                                        <td>{{ $j }}</td>
                                                        <td>{{ $account->number }}</td>
                                                        <td>{{ $account->holder }}</td>
                                                        <td>{{ numberFormat($balance) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td></td>
                                                        <td><i>Net Profit</i></td>
                                                        <td>{{ numberFormat($net_profit) }}</td>
                                                    </tr>
                                                    @php
                                                        $gross_balance += $net_profit;
                                                    @endphp
                                                @elseif ($balance)
                                                    <!-- Asset or Liability -->
                                                    <tr>
                                                        <td>{{ $j }}</td>
                                                        <td>{{ $account->number }}</td>
                                                        <td>{{ $account->holder }}</td>
                                                        <td>{{ numberFormat($balance) }}</td>
                                                    </tr>
                                                @endif
                                            @endif
                                        @endforeach
                                        @php
                                            $balance_cluster[] = compact('type', 'gross_balance');
                                        @endphp
                                        <tr>
                                            @for ($k = 0; $k < 3; $k++)
                                                <td></td>
                                            @endfor
                                            <td><h3 class="text-xl-left">{{ amountFormat($gross_balance) }}</h3></td>
                                        </tr>
                                    </tbody>
                                </table>                                
                            @else
                                <!-- summary -->
                                <h5 class="title {{ $bg_styles[$i] }} p-1 white">{{ $type }} || Asset = Equity  + (Revenue - Expense) + Liability</h5>
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>{{trans('accounts.account_type')}}</th>
                                            <th>Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($balance_cluster as $cluster)
                                            <tr>
                                                <td>{{ $cluster['type'] }}</td>
                                                <td>{{ amountFormat($cluster['gross_balance']) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>    
                            @endif                           
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection