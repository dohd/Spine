<html>
<head>
	<title>Invoice</title>
	<style>
		body {
			font-family: "Times New Roman", Times, serif;
			font-size: 10pt;
		}

		table {
			font-family: "Myriad Pro", "Myriad", "Liberation Sans", "Nimbus Sans L", "Helvetica Neue", Helvetica, Arial, sans-serif;
			font-size: 10pt;
		}

		table thead td {
			background-color: #BAD2FA;
			text-align: center;
			border: 0.1mm solid black;
			font-variant: small-caps;
		}

		td {
			vertical-align: top;
		}

		.bullets {
			width: 8px;
		}

		.items {
			border-bottom: 0.1mm solid black;
			font-size: 10pt;
			border-collapse: collapse;
			width: 100%;
			font-family: sans-serif;
		}

		.items td {
			border-left: 0.1mm solid black;
			border-right: 0.1mm solid black;
		}

		.align-r {
			text-align: right;
		}

		.align-c {
			text-align: center;
		}

		.bd {
			border: 1px solid black;
		}

		.bd-t {
			border-top: 1px solid
		}

		.ref {
			width: 100%;
			font-family: serif;
			font-size: 10pt;
			border-collapse: collapse;
		}

		.ref tr td {
			border: 0.1mm solid #888888;
		}

		.ref tr:nth-child(2) td {
			width: 50%;
		}

		.customer-dt {
			width: 100%;
			font-family: serif;
			font-size: 10pt;
		}

		.customer-dt tr td:nth-child(1) {
			border: 0.1mm solid #888888;
		}

		.customer-dt tr td:nth-child(3) {
			border: 0.1mm solid #888888;
		}

		.customer-dt-title {
			font-size: 7pt;
			color: #555555;
			font-family: sans;
		}

		.doc-title-td {
			text-align: center;
			width: 100%;
		}

		.doc-title {
			font-size: 15pt;
			color: #0f4d9b;
		}

		.doc-table {
			font-size: 10pt;
			margin-top: 5px;
			width: 100%;
		}

		.header-table {
			width: 100%;
			border-bottom: 0.8mm solid #0f4d9b;
		}

		.header-table tr td:first-child {
			color: #0f4d9b;
			font-size: 9pt;
			width: 100%;
		}

		.address {
			color: #0f4d9b;
			font-size: 10pt;
			width: 40%;
			text-align: right;
		}

		.header-table-text {
			color: #0f4d9b;
			font-size: 9pt;
			margin: 0;
		}

		.header-table-child {
			color: #0f4d9b;
			font-size: 8pt;
		}

		.header-table-child tr:nth-child(2) td {
			font-size: 9pt;
			padding-left: 50px;
		}

		.footer {
			font-size: 9pt;
			text-align: center;
		}
	</style>
</head>
<body>
	<htmlpagefooter name="myfooter">
		<div class="footer">
			Page {PAGENO} of {nb}
		</div>
	</htmlpagefooter>
	<sethtmlpagefooter name="myfooter" value="on" />
	<table class="header-table">
		<tr>
			<td>
				<img src="{{ asset('images/letterhead.png') }}" style="object-fit:contain" width="100%"/>
			</td>
		</tr>
	</table>
	<table class="doc-table">
		<tr>
			<td class="doc-title-td">
				<span class='doc-title'>
					<b>INVOICE</b>
				</span>
			</td>
		</tr>
	</table><br>
	<table class="customer-dt" cellpadding="10">
		<tr>
			<td width="50%">
				<span class="customer-dt-title">CUSTOMER DETAILS:</span><br><br>
				<b>Client Name :</b> {{ $resource->customer->company }}<br>
				<b>Client Tax Pin : </b>{{ $resource->customer->taxid }}<br>
				<b>Address :</b> {{ $resource->customer->address }}<br>
				<b>Email :</b> {{ $resource->customer->email }}<br>
				<b>Cell :</b> {{ $resource->customer->phone }}<br>
			</td>
			<td width="5%">&nbsp;</td>
			<td width="45%">
				<span class="customer-dt-title">REFERENCE DETAILS:</span><br><br>				
				<b>Invoice No :</b> {{ sprintf('%04d', $resource->tid) }}<br><br>
				<b>Date :</b> {{ dateFormat($resource->invoicedate, 'd-M-Y') }}<br>
				<b>Overdue after :</b> {{ $resource->validity ? $resource->validity . ' days' : 'On Receipt' }}<br>
				<b>KRA Pin :</b> P051516705D<br>
			</td>
		</tr>
	</table><br>

	<table  class="ref" cellpadding="10">
		<tr><td colspan="2">Ref : <b>{{ $resource->notes }}</b></td></tr>
	</table><br>

	<table class="items" cellpadding="8">
		<thead>
			<tr>
				<td width="6%">No.</td>
				<td width="24%"> REFERENCE</td>
				<td width="24%"> DESCRIPTION</td>
				<td width="8%">QTY</td>
				<td width="8%">UoM</td>
				<td width="14%">RATE</td>
				<td width="14%">AMOUNT(Ksh)</td>
			</tr>
		</thead>
		<tbody>
			@foreach($resource->products as $k => $val)
				<tr>
					<td>{{ $k+1 }}</td>					
					<td>{{ $val->reference }}</td>
					<td>{{ $val->description }}</td>
					<td class="align-c">{{ (int) $val->product_qty }}</td>
					<td class="align-c">{{ $val->unit }}</td>
					<td class="align-r">{{ number_format($val->product_price, 2) }}</td>
					<td class="align-r">{{ number_format($val->product_qty * $val->product_price, 2) }}</td>
				</tr>
			@endforeach
			<!-- 20 dynamic empty rows -->
			@for ($i = count($resource->products); $i < 5; $i++)
				<tr>
					@for($j = 0; $j < 7; $j++) 
						<td></td>
					@endfor
				</tr>
			@endfor
			<!--  -->
			<tr>
				<td colspan="5" class="bd-t" rowspan="2">
					@isset($resource->bank)
						<span class="customer-dt-title">BANK DETAILS:</span><br>
						<b>Account Name :</b> {{ $resource->bank->name }}<br>
						<b>Account Number :</b> {{ $resource->bank->number }}<br>
						<b>Bank :</b> {{ $resource->bank->bank }} &nbsp;&nbsp;<b>Branch :</b> {{ $resource->bank->branch }} <br>
						<b>Currency :</b> Kenya Shillings &nbsp;&nbsp;<b>Swift Code :</b> {{ $resource->bank->code }} <br>
						@php
							$paybill = '';
							switch ($resource->bank->code) {
								case 'KCBLKENX': 
									$paybill = '(KCB Mpesa Paybill: 522 522)';
									break;
								case 'EQBLKENA':
									$paybill = '(Equity Mpesa Paybill: 247 247)';
									break;
								case 'CBAFKENX':
									$paybill = '(NCBA Mpesa Paybill: 880 100)';
									break;
							}
						@endphp
						{{ $paybill }}
					@endisset
				</td>
				<td class="bd align-r">Sub Total:</td>
				@if ($resource->print_type == 'inclusive')
					<td class="bd align-r">{{ number_format($resource->total, 2) }}</td>
				@else
					<td class="bd align-r">{{ number_format($resource->subtotal, 2) }}</td>
				@endif
			</tr>
			<tr>
				@if ($resource->print_type == 'inclusive')
					<td class="align-r">VAT {{ $resource->tax_id }}%</td>
					<td class="align-r">{{ $resource->tax_id ? 'INCLUSIVE' : 'NONE' }}</td>
				@else
					<td class="align-r">Tax {{ $resource->tax_id ? $resource->tax_id . '%' : 'Off' }}</td>
					<td class="align-r">{{ number_format($resource->tax, 2) }}</td>
				@endif
			</tr>
			<tr>
				<td colspan="5">
					<b>Terms: </b> {{ $resource->term? $resource->term->title : '' }}<br>
				</td>
				<td class="bd align-r"><b>Grand Total:</b></td>
				<td class="bd align-r">{{ number_format($resource->total, 2) }}</td>
			</tr>
		</tbody>
	</table>
</body>
</html>