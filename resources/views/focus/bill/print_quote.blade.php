<html>
<head>
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
		}
		.ref tr td {
			border: 0.1mm solid #888888; 
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
			margin-top:5px;
			width: 100%;
		}

		.header-table {
			width: 100%;
			border-bottom: 0.8mm solid #0f4d9b;
		}
		.header-table tr td:first-child {
			color: #0f4d9b;
			font-size: 9pt;
			width: 60%;
			text-align: left;
		}
		.address {
			color: #0f4d9b;
			font-size: 10pt;
			width: 40%;
			text-align: right;
		}
		.header-table-text {
			color:#0f4d9b; 
			font-size:9pt; 
			margin: 0;
		}
		.header-table-child {
			color:#0f4d9b; 
			font-size:8pt;
		}
		.header-table-child tr:nth-child(2) td {
			font-size:9pt; 
			padding-left:50px;
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
	<table class="header-table">
		<tr>
			<td>
				<img src="{{ Storage::disk('public')->url('app/public/img/company/ico/logo.jpg') }}" style="width:350px;" /><br>
				<p class="header-table-text"> Supply, Installation, Maintenance & Repair of:</p>
				<table class="header-table-child">
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
					<tr><td>... and General Suppliers</td></tr>
				</table>								
			</td>
			<td class="address"><br><br>
				Lean Aircons Building, Opp NextGen Mall<br>
				Mombasa Road, Nairobi - Kenya<br>
				P.O Box 36082 - 00200.<br>
				Cell : +254 732 345 393, +254 787 391 015<br>
				info@leanventures.co.ke<br />
				leannventures@gmail.com
			</td>
		</tr>
	</table>
	<table class="doc-table">
		<tr>
			<td class="doc-title-td">
				<span class='doc-title'><b>QUOTATION</b></span>
			</td>
		</tr>
	</table><br>
	<table class="customer-dt" cellpadding="10">
		<tr>
			<td width="50%">
				<span class="customer-dt-title">CUSTOMER DETAILS:</span><br /><br />
				<b>Client Name :</b> {{ $invoice->client->company }}<br />
				<b>Branch :</b> {{ $invoice->branch->name }}<br />
				<b>Address :</b> {{ $invoice->client->address }}<br />
				<b>Email :</b> {{ $invoice->client->email }}<br />
				<b>Cell :</b> {{ $invoice->client->phone }}<br />
				<b>Attention :</b> {{ $invoice->attention }}<br />
			<td width="5%">&nbsp;</td>
			<td width="45%">
				<span class="customer-dt-title">REFERENCE DETAILS:</span><br /><br />
				<b>Date :</b> {{ dateFormat($invoice->invoicedate, 'd-M-Y') }}<br />
				<b>Quotation No :</b> {{ 'QT/' . sprintf('%04d', $invoice->tid) }}<br />
				<b>Valid Till :</b> {{ dateFormat($invoice->invoiceduedate, 'd-M-Y') }} <br />
				<b>Currency :</b> Kenya Shilling <br />
			</td>
		</tr>
	</table><br />
	<table  class="ref" cellpadding="10">
		<tr>
			<td>
				Ref : <b>{{ $invoice->notes }}</b>
			</td>
		</tr>
	</table>
	<br>
	<table class="items" cellpadding="8">
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
				@if ($product->a_type == 2)		
					<tr>
						<td><b>{{ $product->numbering }}<b></td>
						<td><b>{{ $product->product_name }}</b></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
					</tr>
				@else
					<tr>
						<td>{{ $product->numbering }}</td>
						<td>{{ $product->product_name }}</td>
						<td class="align-c">{{ (int) $product->product_qty }}</td>
						<td class="align-c">{{ $product->unit }}</td>
						<td class="align-r">{{ number_format($product->product_price, 2) }}</td>
						<td class="align-r">{{ number_format($product->product_subtotal, 2) }}</td>
					</tr>
				@endif
			@endforeach
			<tr>
				<td colspan="4" class="bd-t"></td>
				<td class="bd align-r">Sub Total:</td>
				<td class="bd align-r">{{ number_format($invoice->subtotal, 2) }}</td>			
			</tr>
			<tr>
				<td colspan="4"></td>
				<td class="align-r">Tax 16%</td>
				<td class="align-r">{{ number_format($invoice->tax, 2) }}</td>
			</tr>
			<tr>
				<td colspan="4"><em>Prepared By : </em><b>{{ $invoice->prepared_by }}</b></td>
				<td class="bd align-r"><b>Grand Total:</b></td>
				<td class="bd align-r">{{ number_format($invoice->total, 2) }}</td>
			</tr>
		</tbody>
	</table>
</body>
</html>
