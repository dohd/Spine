@extends ('core.layouts.app')
@section ('title', 'Profit & Loss | ' . trans('labels.backend.accounts.management'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h3> 
                Profit & Loss
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
                @foreach(['Income', 'COG', 'Expense', 'Summary'] as $i => $type)
                    <div class="card">
                        <div class="card-content print_me">
                            @if ($i < 3)
                                <h5 class="title {{ $bg_styles[$i] }} p-1 white">
                                    @php
                                        if ($type == 'Income') echo 'Revenue';
                                        elseif ($type == 'COG') echo $type;
                                        else echo 'Direct ' . $type;
                                    @endphp
                                </h5>
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
                                            @php
                                                $is_revenue = $i == 0 && $account->account_type == $type;
                                                $is_cog = $i == 1 && $account->system == 'cog';
                                                $is_dir_expense = $i == 2 && $account->account_type == $type && $account->system != 'cog';
                                            @endphp
                                            @if ($is_revenue || $is_cog || $is_dir_expense)                                          
                                                @php
                                                    $balance = 0;
                                                    $debit = $account->transactions->sum('debit');
                                                    $credit = $account->transactions->sum('credit');
                                                    if ($type == 'Income') $balance = $credit;
                                                    else $balance = $debit;
                                                    $gross_balance += $balance;
                                                    $j++;
                                                @endphp
                                                <tr>
                                                    <td>{{ $j }}</td>
                                                    <td>{{ $account->number }}</td>
                                                    <td>{{ $account->holder }}</td>
                                                    <td>{{ numberFormat($balance) }}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                        <tr>
                                            @for ($k = 0; $k < 3; $k++)
                                                <td></td>
                                            @endfor
                                            <td><h3 class="text-xl-left">{{ amountFormat($gross_balance) }}</h3></td>
                                        </tr>
                                        @php
                                            $balance_cluster[] = compact('type', 'gross_balance');
                                        @endphp
                                    </tbody>
                                </table>                                
                            @else
                                <h5 class="title {{ $bg_styles[$i] }} p-1 white">{{ $type }}</h5>
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>{{trans('accounts.account_type')}}</th>
                                            <th>Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                            @php
                                                $gross_profit = $balance_cluster[0]['gross_balance'] - $balance_cluster[1]['gross_balance'];
                                                $net_profit = $gross_profit - $balance_cluster[2]['gross_balance'];
                                            @endphp
                                            <tr>
                                                <td>Revenue</td>
                                                <td><h5>{{ amountFormat($balance_cluster[0]['gross_balance']) }}</h5></td>
                                            </tr>
                                            <tr>
                                                <td>{{ $balance_cluster[1]['type'] }}</td>
                                                <td><h5>- {{ amountFormat($balance_cluster[1]['gross_balance']) }}</h5></td>
                                            </tr>
                                            <tr style="border-top: 2px solid grey;">
                                                <td><i>Gross Profit</i></td>
                                                <td><h5><b>{{ amountFormat($gross_profit) }}</b></h5></td>
                                            </tr>
                                            <tr>
                                                <td>Direct Expense</td>
                                                <td><h5>-{{ amountFormat($balance_cluster[2]['gross_balance']) }}</h5></td>
                                            </tr>
                                            <tr style="border-top: 2px solid grey;">
                                                <td><i>Net Profit</i></td>
                                                <td><h5><b>{{ amountFormat($net_profit) }}</b></h5></td>
                                            </tr>
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