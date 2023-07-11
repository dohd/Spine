@extends ('core.layouts.app')
@section ('title', 'Trial Balance | ' . trans('labels.backend.accounts.management'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <div class="row">
                <div class="col-4">
                    <h3>
                        Trial Balance
                        <a href="{{ route('biller.accounts.trial_balance', 'p') }}" class="btn btn-success btn-sm" target="_blank" id="print">
                            <i class="fa fa-print"></i> {{ trans('general.print') }}
                        </a>
                    </h3>
                </div>
                <div class="col-8">
                    <div class="row no-gutters">
                        <div class="col-4 text-right mr-1"><h5>Balance as At</h5></div>
                        <div class="col-3 mr-1"><input type="text" id="end_date" class="form-control form-control-sm datepicker end_date"></div>
                        <div class="col-4">
                            <a href="{{ route('biller.accounts.trial_balance', 'v') }}" class="btn btn-info btn-sm search" id="search4">Search</a>
                            <a href="{{ route('biller.accounts.trial_balance', 'v') }}" class="btn btn-success btn-sm refresh" id="refresh">
                                <i class="fa fa-refresh" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
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
                                        $debit = $account->transactions()
                                        ->when(@$date, fn($q) => $q->whereDate('tr_date', '<=', $date))
                                        ->sum('debit');
                                        
                                        $credit = $account->transactions()
                                        ->when(@$date, fn($q) => $q->whereDate('tr_date', '<=', $date))
                                        ->sum('credit');

                                        $debit_balance = 0;
                                        $credit_balance = 0;
                                        if (in_array($account->account_type, ['Asset', 'Expense'], 1)) {
                                            $debit_balance = round($debit - $credit, 4);
                                            if ($debit_balance < 0) {
                                                $credit_balance = $debit_balance * - 1;
                                                $debit_balance = 0;
                                            }
                                        }                                            
                                        if (in_array($account->account_type, ['Income', 'Liability', 'Equity'], 1)) {
                                            $credit_balance = round($credit - $debit, 4); 
                                            if ($credit_balance < 0) {
                                                $debit_balance = $credit_balance * - 1;
                                                $credit_balance = 0;
                                            }
                                        }
                                        $debit_total += $debit_balance;
                                        $credit_total += $credit_balance;                                   
                                    @endphp
                                    @if ($debit_balance > 0 || $credit_balance > 0)
                                        <tr>                                        
                                            <td>{{ $i+1 }}</td>
                                            <td>{{ $account->number }}</td>
                                            <td>{{ $account->holder }}</td>
                                            <td>{{ $debit_balance > 0 ? numberFormat($debit_balance) : '' }}</td>
                                            <td>{{ $credit_balance > 0 ? numberFormat($credit_balance) : '' }}</td>
                                        </tr> 
                                    @endif
                                @endforeach
                                <tr>
                                    @for ($i = 0; $i < 3; $i++)
                                        <td></td>
                                    @endfor 
                                    @foreach ([$debit_total, $credit_total] as $i => $val)
                                        <td>
                                            <span class="text-xl-left h3">{{ amountFormat($val) }}</span>&nbsp;
                                            @if (!$i && round($debit_total - $credit_total))
                                                <span class="text-danger h5">({{ $debit_total - $credit_total }})</span>
                                            @endif
                                        </td>
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

@section('after-scripts')
<script>
    // datepicker
    $('.datepicker')
    .datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    .datepicker('setDate', new Date());
    const date = @json(($date));
    if (date) {
        $('.datepicker').datepicker('setDate', new Date(date));
        const queryStr = '?end_date=' + $('#end_date').val();
        const printUrl = "{{ route('biller.accounts.trial_balance', 'p') }}" + queryStr;
        $('#print').attr('href', printUrl);
    }

    // filter by date
    $('#end_date').change(function() {
        const queryStr = '?end_date=' + $('#end_date').val();
        const viewUrl = "{{ route('biller.accounts.trial_balance', 'v') }}" + queryStr;
        $('#search4').attr('href', viewUrl);
    });
</script>
@endsection