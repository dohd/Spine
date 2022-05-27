<html>
    <head>
        <title>Balance Sheet</title>
    </head>
    <style>
		body {
			font-family: "Times New Roman", Times, serif;
			font-size: 10pt;
		}
        h5 {
			font-size: 1em;
			font-family: Arial, Helvetica, sans-serif;
			font-weight: bold;
            margin-bottom: .7em;
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
        <h2>Balance Sheet as at {{ $dates[1]? dateFormat($dates[1]) : date('d-m-Y') }}</h2>
    </div>

    @php
        $balance_cluster = array();
    @endphp
    @foreach(['Asset', 'Equity', 'Liability', 'Summary'] as $i => $type)
        @if ($i < 3)
            <h5>{{ $type }} {{trans('accounts.accounts')}}</h5>
            <table class="table table-items" cellpadding=8>
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
                                <tr class="dotted">
                                    <td>{{ $j }}</td>
                                    <td>{{ $account->number }}</td>
                                    <td>{{ $account->holder }}</td>
                                    <td style="text-align: center;">{{ numberFormat($balance) }}</td>
                                </tr>
                                <tr class="dotted">
                                    <td></td>
                                    <td></td>
                                    <td><i>Net Profit</i></td>
                                    <td style="text-align: center;">{{ numberFormat($net_profit) }}</td>
                                </tr>
                                @php
                                    $gross_balance += $net_profit;
                                @endphp
                            @elseif ($balance)
                                <!-- Asset or Liability -->
                                <tr class="dotted">
                                    <td>{{ $j }}</td>
                                    <td>{{ $account->number }}</td>
                                    <td>{{ $account->holder }}</td>
                                    <td style="text-align: center;">{{ numberFormat($balance) }}</td>
                                </tr>
                            @endif
                        @endif
                    @endforeach
                    @php
                        $balance_cluster[] = compact('type', 'gross_balance');
                    @endphp
                    <tr class="dotted">
                        @for ($k = 0; $k < 3; $k++)
                            <td></td>
                        @endfor
                        <td style="text-align: center;"><h3 class="text-xl-left">{{ amountFormat($gross_balance) }}</h3></td>
                    </tr>
                </tbody>
            </table>                                
        @else
            <!-- summary -->
            <h5>{{ $type }} || Asset = Equity  + (Revenue - Expense) + Liability</h5>
            <table class="table table-items" cellpadding=8>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{trans('accounts.account_type')}}</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($balance_cluster as $k => $cluster)
                        <tr class="dotted">
                            <td>{{ $k+1 }}</td>
                            <td>{{ $cluster['type'] }}</td>
                            <td style="text-align: center;">{{ amountFormat($cluster['gross_balance']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>    
        @endif                           
    @endforeach
</body>
</html>