<html>
    <head>
        <title>Trial Balance</title>
    </head>
    <style>
		body {
			font-family: "Times New Roman", Times, serif;
			font-size: 10pt;
		}
		p {
			margin: 0pt;
		}
		table.items {
			border: 0.1mm solid #000000;
		}
		table {
			font-family: "Myriad Pro", "Myriad", "Liberation Sans", "Nimbus Sans L", "Helvetica Neue", Helvetica, Arial, sans-serif;
			font-size: 10pt;
		}
		td {
			vertical-align: top;
		}
		.items td {
			border-left: 0.1mm solid #000000;
			border-right: 0.1mm solid #000000;
		}
		table thead th {
			background-color: #BAD2FA;
			text-align: center;
			border: 0.1mm solid #000000;
			font-weight: normal;
		}
		        
        .dotted td {
			border-bottom: dotted 1px black;
		}
		.dottedt th {
			border-bottom: dotted 1px black;
		}

		.footer {
			font-size: 9pt; 
			text-align: center; 
		}
		.table-items {
			font-size: 10pt; 
			border-collapse: collapse;
			height: 700px;
			width: 100%;
		}
	</style>
</head>
<body>
	<htmlpagefooter name="myfooter">
		<div class="footer">Page {PAGENO} of {nb}</div>
	</htmlpagefooter>
	<sethtmlpagefooter name="myfooter" value="on" />

    <div style="text-align: center; line-height: 0">
        <h1>Lean Ventures</h1>
        <h2>Trial Balance as at {{ $dates[1]? dateFormat($dates[1]) : date('d-m-Y') }}</h2>
    </div>

    <table class="table table-items" cellpadding=8>
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
                    <tr class="dotted">
                        <td>{{ $i+1 }}</td>
                        <td>{{ $account->number }}</td>
                        <td>{{ $account->holder }}</td>
                        <td style="text-align: center;">{{ $debit_balance > 0 ? numberFormat($debit_balance) : '' }}</td>
                        <td style="text-align: center;">{{ $credit_balance > 0 ? numberFormat($credit_balance) : '' }}</td>
                    </tr> 
                @endif
            @endforeach
            <tr class="dotted">
                @for ($i = 0; $i < 3; $i++)
                    <td></td>
                @endfor 
                @foreach ([$debit_total, $credit_total] as $i => $val)
                    <td>
                        <h3>{{ amountFormat($val) }}</h3>&nbsp;
                        @if (!$i && round($debit_total - $credit_total))
                            <h5 style="color:red">({{ $debit_total - $credit_total }})</h5>
                        @endif
                    </td>
                @endforeach                                       
            </tr>
        </tbody>
    </table>
</body>
</html>