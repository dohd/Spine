<html>
<head>
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
		.items td.totals {
			text-align: right;
			border: 0.1mm solid #000000;
		}
		.items td.totalsss {
			text-align: right;
		}
		.items td.mytotals {
			text-align: left;
			border: 0.1mm solid #000000;
		}
		.items td.mytotalss {
			text-align: left;
		}
		.items td.totalss {
			text-align: right;
			border: 0.1mm solid #000000;
			text-transform: uppercase;
		}
		.items td.cost {
			text-align: center;
		}
		.dotted td {
			border-bottom: dotted 1px black;
		}
		.dotted th {
			border-bottom: dotted 1px black;
		}
		h5 {
			text-decoration: underline;
			font-size: 1em;
			font-family: Arial, Helvetica, sans-serif;
			font-weight: bold;
		}
		h5 span {
			text-decoration: none;
		}
		.footer {
			font-size: 9pt; 
			text-align: center; 
		}
		.items-table {
			font-size: 10pt; 
			border-collapse: collapse;
			height: 700px;
			width: 100%;
		}
	</style>
    <title>Statement On Invoice</title>
</head>
<body>
	<htmlpagefooter name="myfooter">
		<div class="footer">Page {PAGENO} of {nb}</div>
	</htmlpagefooter>
	<sethtmlpagefooter name="myfooter" value="on" />
	
	<table class="header-table">
		<tr>
			<td>
				<img src="{{ Storage::disk('public')->url('app/public/img/company/' . $company->logo) }}" style="object-fit:contain" width="100%"/>
			</td>
		</tr>
	</table>
	
	<table width="100%" style="font-size: 10pt;margin-top:5px;">
		<tr>
			<td style="text-align: center;" width="100%" class="headerData">
				<span style="font-size:15pt;color:#0f4d9b;"><b>{{ strtoupper('Statement On Invoice') }}</b></span>
			</td>
		</tr>
	</table><br>

	<table class="items items-table" cellpadding=8>
		<thead>
			<tr>
				<th>#</th>
				<th>Date</th>
				<th>Type</th>
				<th>Description</th>
				<th>Invoice Amount</th>
				<th>Amount Paid</th>
				<th>Invoice Balance</th>
			</tr>
		</thead>
		<tbody>
			@php
				$balance = 0;
			@endphp
			@foreach($inv_statements as $i => $row)
				<tr class="dotted">
					<td class="mytotalss">{{ $i+1 }}</td>
					<td class="mytotalss">{{ dateFormat($row->date) }}</td>
					<td class="mytotalss">{{ $row->type }}</td>
					<td class="mytotalss">{{ $row->note }}</td>
					<td class="mytotalss">{{ numberFormat($row->debit) }}</td>
					<td class="mytotalss">{{ numberFormat($row->credit) }}</td>
					<td class="mytotalss">
						@php
							if ($row->type == 'invoice') 
                                $balance = $row->debit;
                            else $balance -= $row->credit;
                            echo numberFormat($balance);
						@endphp
					</td>
				</tr>
			@endforeach
			<!-- END ITEMS HERE -->
		</tbody>
	</table>
</body>
</html>
