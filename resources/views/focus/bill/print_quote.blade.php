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
		table.itemsboarder {
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
		table thead td {
			background-color: #BAD2FA;
			text-align: center;
			border: 0.1mm solid #000000;
			font-variant: small-caps;
		}
		.items td.blanktotal {
			background-color: #EEEEEE;
			border: 0.1mm solid #000000;
			background-color: #FFFFFF;
			border: 0mm none #000000;
			border-top: 0.1mm solid #000000;
			border-right: 0.1mm solid #000000;
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
			text-align: "."center;
		}
		.invoice-title h1 {
			font-size: 50px;
			font-weight: lighter;
			text-align: center;
			margin: 0;
			text-transform: uppercase;
			padding: 5px;
			letter-spacing: 2px;
		}
		.itemss {
			text-transform: uppercase;
		}
		h5 {
			text-decoration: underline;
		}
		.bullets {
			width: 8px;
		}
		/* .top-row td {
			border-top: 1pt solid black;
		} */
		.items tbody tr:nth-last-child(3) td {
			border-top: 1pt solid black;
		}
	</style>
</head>
<body>
	<htmlpagefooter name="myfooter">
		<div style=" font-size: 9pt; text-align: center; padding-top: 3mm; ">
			Page {PAGENO} of {nb}
		</div>
	</htmlpagefooter>
	<sethtmlpagefooter name="myfooter" value="on" />
	<table width="100%" style="border-bottom: 0.8mm solid #0f4d9b;">
		<tr>
			<td width="60%" align="left" style="color:#0f4d9b;font-size:9pt;">
				<img src="{{ Storage::disk('public')->url('app/public/img/company/ico/logo.jpg') }}" style="width:350px;" /><br>
				<p style="color:#0f4d9b;font-size:9pt;"> Supply, Installation, Maintenance & Repair of:</p>
				<table style="color:#0f4d9b; font-size:8pt;">
					<tr>
						<td>
							<div><img src="{{ Storage::disk('public')->url('app/public/img/company/ico/bullets.png') }}" class="bullets" /> Air Conditioners & Refrigerators </div>
							<div><img src="{{ Storage::disk('public')->url('app/public/img/company/ico/bullets.png') }}" class="bullets" /> Coldrooms & Chillers </div>
							<div><img src="{{ Storage::disk('public')->url('app/public/img/company/ico/bullets.png') }}" class="bullets" /> Mechanical Ventilation Systems</div>
						</td>
						<td>
							<div><img src="{{ Storage::disk('public')->url('app/public/img/company/ico/bullets.png') }}" class="bullets" /> Laboratory Fume Cupboards</div>
							<div><img src="{{ Storage::disk('public')->url('app/public/img/company/ico/bullets.png') }}" class="bullets" /> Steam Bath and Saunas</div>
							<div><img src="{{ Storage::disk('public')->url('app/public/img/company/ico/bullets.png') }}" class="bullets" /> Raised Floors / Access Panels</div>
						</td>
					</tr>
					<tr><td style="font-size:9pt; padding-left:50px;">... and General Suppliers</td></tr>
				</table>								
			</td>
			<td width="40%" align="right" style="color:#0f4d9b;font-size:10pt;"><br><br>
				Lean Aircons Building, Opp NextGen Mall<br>
				Mombasa Road, Nairobi - Kenya<br>
				P.O Box 36082 - 00200.<br>
				Cell : +254 732 345 393, +254 787 391 015<br>
				info@leanventures.co.ke<br />
				leannventures@gmail.com
			</td>
		</tr>
	</table>
	<table width="100%" style="font-size: 10pt;margin-top:5px;">
		<tr>
			<td style="text-align: center;" width="100%" class="headerData"><span style="font-size:15pt;color:#0f4d9b;text-transform: uppercase;">
				<b>QUOTATION</b></span>
			</td>
		</tr>
	</table><br>
	<table width="100%" style="font-family: serif;font-size:10pt;" cellpadding="10">
		<tr>
			<td width="50%" style="border: 0.1mm solid #888888;">
				<span style="font-size: 7pt; color: #555555; font-family: sans;">CUSTOMER DETAILS:</span><br /><br />
				<b>Client Name :</b> {{ $invoice->client->company }}<br />
				<b>Branch :</b> {{ $invoice->branch->name }}<br />
				<b>Address :</b> {{ $invoice->client->address }}<br />
				<b>Email :</b> {{ $invoice->client->email }}<br />
				<b>Cell :</b> {{ $invoice->client->phone }}<br />
				<b>Attention :</b> {{ $invoice->attention }}<br />
			<td width="5%">&nbsp;</td>
			<td width="45%" style="border: 0.1mm solid #888888;"><span style="font-size: 7pt; color: #555555; font-family: sans;">REFERENCE DETAILS:</span><br /><br />
				<b>Date :</b> {{ dateFormat($invoice->invoicedate, 'd-M-Y') }}<br />
				@php
				 	/** Prepend initial zeroes */
					$tid = $invoice->tid;
					switch(strlen(''.$tid)) {
						case 1:
							$tid = sprintf('%04d', $tid);
							break;
						case 2:
							$tid = sprintf('%04d', $tid);
							break;
						case 3:
							$tid = sprintf('%04d', $tid);
							break;
					}
				@endphp
				<b>Quotation No :</b> {{ 'QT/' . $tid }}<br />
				<b>Valid Till :</b> {{ dateFormat($invoice->invoiceduedate, 'd-M-Y') }} <br />
				<b>Currency :</b> Kenya Shilling <br />
			</td>
		</tr>
	</table><br />
	<table width="100%" style="font-family: serif;font-size:10pt;" cellpadding="10">
		<tr>
			<td style="border: 0.1mm solid #888888; ">
				Ref : <b>{{ $invoice->notes }}</b>
			</td>
		</tr>
	</table>
	<br>
	<table class="items" width="100%" style="font-size: 10pt; border-collapse: collapse;" cellpadding="8">
		<thead>
			<tr>
				<td width="8%">No.</td>
				<td width="42%">ITEM DESCRIPTION</td>
				<td width="10%">QTY</td>
				<td width="10%">UoM</td>
				<td width="15%">RATE</td>
				<td width="15%">AMOUNT</td>
			</tr>
		</thead>
		<tbody>
			@foreach($invoice->products as $product)
				<tr>
					<td>{{ $product->numbering }}</td>
					<td>{{ $product->product_name }}</td>
					<td>{{ (int) $product->product_qty }}</td>
					<td>{{ $product->unit }}</td>
					<td>{{ number_format($product->product_price, 2) }}</td>
					<td>{{ number_format($product->product_subtotal, 2) }}</td>
				</tr>
			@endforeach
			<tr class="top-row">
				<td colspan="4"></td>
				<td>Sub Total:</td>
				<td>{{ number_format($invoice->subtotal, 2) }}</td>				
			</tr>
			<tr>
				<td colspan="4"></td>
				<td>Tax 16%</td>
				<td>{{ number_format($invoice->tax, 2) }}</td>
			</tr>
			<tr>
				<td colspan="4">Prepared By : <b>{{ $invoice->prepared_by }}</b></td>
				<td>Grand Total</td>
				<td>{{ number_format($invoice->total, 2) }}</td>
			</tr>
		</tbody>
	</table>
</body>
</html>
