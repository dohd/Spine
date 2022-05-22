@extends ('core.layouts.app')
@section ('title', trans('accounts.balance_sheet').' | ' . trans('labels.backend.accounts.management'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h3> 
                {{trans('accounts.balance_sheet')}} 
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
                @php
                    $balance_cluster = array();
                @endphp
                @foreach(['Asset', 'Equity', 'Expense', 'Income', 'Liability', 'Summary'] as $i => $type)
                    <div class="card">
                        <div class="card-content print_me">
                            @if ($i < 5)
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
                                            $j = 1;
                                        @endphp
                                        @foreach ($accounts as $account)
                                            @if ($account->account_type == $type)
                                                <tr>
                                                    <td>{{ $j }}</td>
                                                    <td>{{ $account->number }}</td>
                                                    <td>{{ $account->holder }}</td>
                                                    <td>{{ $account->balance }}</td>
                                                </tr>
                                                @php
                                                    $gross_balance += $account->balance;
                                                    $j++;
                                                @endphp
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