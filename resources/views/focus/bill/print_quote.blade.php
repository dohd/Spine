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
	@php
		$is_verified = request()->getQueryString();
	@endphp
	<table class="doc-table">
		<tr>
			<td class="doc-title-td">
				@if ($is_verified)
					<span class='doc-title'><b>WORKDONE VERIFICATION</b></span>
				@elseif ($invoice->bank_id)
					<span class='doc-title'><b>PROFORMA INVOICE</b></span>
				@else
					<span class='doc-title'><b>QUOTATION</b></span>
				@endif
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
			</td>
			<td width="5%">&nbsp;</td>
			<td width="45%">
				<span class="customer-dt-title">REFERENCE DETAILS:</span><br /><br />
				<b>Date :</b> {{ dateFormat($invoice->invoicedate, 'd-M-Y') }}<br />
				@php
					$tid = sprintf('%04d', $invoice->tid);
					$field_name = 'Quotation No';
					$field_value = 'QT/' . $tid;
					if ($invoice->bank_id) {
						$field_name = 'Proforma No';
						$field_value = 'PI/' . $tid;
					}
					if ($is_verified) {
						$field_name = 'Verification No';
						$v_no =  ' (v' . $invoice->verified_jcs[0]->verify_no . ')';
						$field_value = $field_value . $v_no;
					}
				@endphp
				<b>{{ $field_name }} :</b> {{ $field_value }}<br />
				@if ($is_verified)
					<b>Verification Date :</b> {{ dateFormat($invoice->verification_date, 'd-M-Y') }} <br />
				@else
					<b>Valid Till :</b> {{ dateFormat($invoice->invoiceduedate, 'd-M-Y') }} <br />
				@endif
				<b>Currency :</b> Kenya Shillings <br />
				<b>Client Ref: </b> {{ $invoice->client_ref }}
			</td>
		</tr>
	</table><br />
	<table  class="ref" cellpadding="10">
		<tr>
			<td colspan="2">
				Ref : <b>{{ $invoice->notes }}</b>
			</td>
		</tr>
		@if ($is_verified)
			<tr>
				<td>RJC :
					<b>
						@foreach ($invoice->verified_jcs as $jc)
							{{ $jc->reference }},
						@endforeach
					</b> 
				</td>
				<td>DNOTE : </td>
			</tr>
		@endif
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
		@php
			$products = $invoice->products;
			if ($is_verified) {
				$products = $invoice->verified_items;
			}
		@endphp
		<tbody>
			@foreach($products as $product)
				@if ($product->a_type == 1)	
					@php
						$product_qty = (int) $product->product_qty;
						$product_subtotal = (int) $product->product_subtotal;
						$product_price = (int) $product->product_price;
					@endphp
					<tr>
						<td>{{ $product->numbering }}</td>
						<td>{{ $product->product_name }}</td>
						<td class="align-c">{{ $product_qty }}</td>
						<td class="align-c">{{ $product->unit }}</td>
						@if ($invoice->print_type == 'inclusive')
							<td class="align-r">{{ number_format($product_subtotal, 2) }}</td>
							<td class="align-r">{{ number_format($product_qty * $product_subtotal, 2) }}</td>
						@else
							<td class="align-r">{{ number_format($product_price, 2) }}</td>
							<td class="align-r">{{ number_format($product_qty * $product_price, 2) }}</td>
						@endif
					</tr>
				@else
					<tr>
						<td><b>{{ $product->numbering }}<b></td>
						<td><b>{{ $product->product_name }}</b></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
					</tr>
				@endif				
			@endforeach
			<!-- empty row -->
			<tr>
				<td {!! 'style="height:'. strval(400-30*count($products)) .'px;"' !!}></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td colspan="4" class="bd-t" rowspan="2">
					@if ($invoice->bank_id)
						<span class="customer-dt-title">BANK DETAILS:</span><br />
						<b>Account Name :</b> Lean Ventures Limited<br />
						<b>Account Number :</b> 1267496231<br />
						<b>Bank :</b> KCB &nbsp;&nbsp;<b>Branch :</b> Nextgen Mall <br />
						<b>Currency :</b> Kenya Shillings &nbsp;&nbsp;<b>Swift Code :</b> KCBLKENX <br />
						(KCB Mpesa Paybill: 522 522)
					@endif
				</td>
				<td class="bd align-r">Sub Total:</td>
				@if ($invoice->print_type == 'inclusive')
					<td class="bd align-r">{{ number_format($invoice->total, 2) }}</td>			
				@else
					<td class="bd align-r">{{ number_format($invoice->subtotal, 2) }}</td>
				@endif
			</tr>
			<tr>
				@if ($invoice->print_type == 'inclusive')
					<td class="align-r">VAT {{ $invoice->tax_id }}%</td>
					<td class="align-r">INCLUSIVE</td>
				@else
					<td class="align-r">Tax {{ $invoice->tax_id }}%</td>
					<td class="align-r">{{ number_format($invoice->tax, 2) }}</td>
				@endif
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
