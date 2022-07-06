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
                        $k = 0;
                    @endphp
                    @foreach ($accounts as $account)
                        @php
                            $balance = 0;
                            $debit = $account->transactions->sum('debit');
                            $credit = $account->transactions->sum('credit');
                            if ($type == 'Asset') {
                                $balance = round($debit - $credit, 2);
                                if ($balance < 0) $balance = 0;
                            } elseif ($type == 'Liability') {
                                $balance = $credit - $debit;
                                if ($balance < 0) $balance = 0;
                            } else $balance = $credit;
                        @endphp
                        @if ($balance)
                            <!-- Equity -->
                            @if ($i == 1)
                                @php                                                    
                                    if ($k == 1) continue;
                                @endphp
                                @if ($account->account_type == $type)  
                                    @php                                                
                                        $gross_balance += $balance;
                                        $j++;
                                    @endphp                                  
                                    <tr class="dotted">
                                        <td>{{ $j }}</td>
                                        <td>{{ $account->number }}</td>
                                        <td>{{ $account->holder }}</td>
                                        <td style="text-align: center;">{{ numberFormat($balance) }}</td>
                                    </tr>
                                @else  
                                    <!-- P&L -->
                                    <tr class="dotted">
                                        <td></td>
                                        <td></td>
                                        <td><i>Net Profit</i></td>
                                        <td style="text-align: center;">{{ numberFormat($net_profit) }}</td>
                                    </tr>
                                    @php
                                        $gross_balance += $net_profit;
                                        $k++;
                                    @endphp
                                @endif
                            @elseif (in_array($i, [0, 2], 1) && $account->account_type == $type)
                                <!-- Asset or Liability -->
                                @php                                                
                                    $gross_balance += $balance;
                                    $j++;
                                @endphp   
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
            <h5>{{ $type }} <br>Asset = Equity  + (Revenue - Expense) + Liability</h5>
            <table class="table table-items" cellpadding=8>
                <tbody>
                    @php
                        $asset_bal = $balance_cluster[0]['gross_balance'];
                        $equity_bal = $balance_cluster[1]['gross_balance'];
                        $liability_bal = $balance_cluster[2]['gross_balance'];
                    @endphp   
                    <tr class="dotted">
                        <td>
                            <h3>
                                {{ numberFormat($equity_bal + $liability_bal) }} = {{ numberFormat($equity_bal) }} + {{ numberFormat($liability_bal) }} <br>
                                <span style="visibility: hidden;">{{ numberFormat($equity_bal + $liability_bal) }}</span> = {{ numberFormat($equity_bal + $liability_bal) }}
                            </h3>
                        </td>
                    </tr>                    
                </tbody>
            </table>    
        @endif                           
    @endforeach
</body>
</html>