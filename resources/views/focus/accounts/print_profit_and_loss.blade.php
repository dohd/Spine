<html>
    <head>
        <title>Profit & Loss</title>
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

    <div style="text-align: center;">
        <h1>{{ auth()->user()->business->cname }}</h1>
        @if ($dates)
            <h2>Profit & Loss for {{ dateFormat($dates['start_date']) }} to {{ dateFormat($dates['end_date']) }}</h2>
        @else
            <h2>Profit & Loss as at {{ date('d-m-Y') }}</h2>
        @endif
    </div>

    @php
        $balance_cluster = array();
    @endphp
    @foreach(['Income', 'COG', 'Expense', 'Summary'] as $i => $type)
        @if ($i < 3)
            <h5>
                @php
                    if ($type == 'Income') echo 'Revenue';
                    elseif ($type == 'COG') echo $type;
                    else echo 'Indirect ' . $type;
                @endphp
            </h5>
            <table class="table table-items" cellpadding="8">
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
                                $dates = [$dates['start_date'],$dates['end_date']];

                                $debit = $account
                                ->transactions()
                                ->when(@$dates, fn($q) => $q->whereBetween('tr_date', $dates))
                                ->sum('debit');

                                $credit = $account
                                ->transactions()
                                ->when(@$dates, fn($q) => $q->whereBetween('tr_date', $dates))
                                ->sum('credit');
                                
                                if ($type == 'Income') {
                                    $credit_balance = round($credit - $debit, 2);
                                    $balance = $credit_balance;
                                } else {
                                    $debit_balance = round($debit - $credit, 2); 
                                    $balance = $debit_balance;
                                }

                                $gross_balance += $balance;
                                $j++;
                            @endphp
                            @if ($balance)
                                <tr class="dotted">
                                    <td>{{ $j }}</td>
                                    <td>{{ $account->number }}</td>
                                    <td>{{ $account->holder }}</td>
                                    <td style="text-align: center;">{{ numberFormat($balance) }}</td>
                                </tr>
                            @endif
                        @endif
                    @endforeach
                    <tr class="dotted">
                        @for ($k = 0; $k < 3; $k++)
                            <td></td>
                        @endfor
                        <td style="text-align: center;"><h3>{{ amountFormat($gross_balance) }}</h3></td>
                    </tr>
                    @php
                        $balance_cluster[] = compact('type', 'gross_balance');
                    @endphp
                </tbody>
            </table>                                
        @else
            <h5>{{ $type }}</h5>
            <table class="table table-items" cellpadding="8">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{trans('accounts.account_type')}}</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $gross_profit = $balance_cluster[0]['gross_balance'] - $balance_cluster[1]['gross_balance'];
                        $net_profit = $gross_profit - $balance_cluster[2]['gross_balance'];
                    @endphp
                    <tr class="dotted">
                        <td>1</td>
                        <td>Revenue</td>
                        <td style="text-align: center;">{{ amountFormat($balance_cluster[0]['gross_balance']) }}</td>
                    </tr>
                    <tr class="dotted">
                        <td>2</td>
                        <td>{{ $balance_cluster[1]['type'] }}</td>
                        <td style="text-align: center;">- {{ amountFormat($balance_cluster[1]['gross_balance']) }}</td>
                    </tr>
                    <tr class="dotted">
                        <td></td>
                        <td><i>Gross Profit</i></td>
                        <td style="text-align: center;"><h5><b>{{ amountFormat($gross_profit) }}</b></h5></td>
                    </tr>
                    <tr class="dotted">
                        <td>3</td>
                        <td>Indirect Expense</td>
                        <td style="text-align: center;">- {{ amountFormat($balance_cluster[2]['gross_balance']) }}</td>
                    </tr>
                    <tr class="dotted">
                        <td></td>
                        <td><i>Net Profit</i></td>
                        <td style="text-align: center;"><h5><b>{{ amountFormat($net_profit) }}</b></h5></td>
                    </tr>
                </tbody>
            </table>   
        @endif                           
    @endforeach
</body>
</html>