<html>
<head>
	<title>Installation List</title>
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
				Cell : +254 732 345 393, +254 713 773 333<br>
				info@leanventures.co.ke<br>
				leannventures@gmail.com
			</td>
		</tr>
	</table>
	<table class="doc-table">
		<tr>
			<td class="doc-title-td">
				<span class='doc-title'>
					<b>Installation List</b>
				</span>				
			</td>
		</tr>
	</table><br>
	<table class="customer-dt" cellpadding="10">
		<tr>
			<td width="50%">
				<span class="customer-dt-title">CUSTOMER DETAILS:</span><br><br>
				@php
					$clientname = $invoice->lead->client_name;
					$branch = 'Head Office';
					$address = $invoice->lead->client_address;
					$email = $invoice->lead->client_email;
					$cell = $invoice->lead->client_contact;
					if ($invoice->client) {
						$clientname = $invoice->client->company;						
						$branch = $invoice->branch->name;
						$address = $invoice->client->address;
						$email = $invoice->client->email;
						$cell = $invoice->client->phone;
					}					
				@endphp
				<b>Client Name :</b> {{ $clientname }}<br>
				<b>Branch :</b> {{ $branch }}<br>
				<b>Address :</b> {{ $address }}<br>
				<b>Email :</b> {{ $email }}<br>
				<b>Cell :</b> {{ $cell }}<br>
				<b>Attention :</b> {{ $invoice->attention }}<br>
			</td>
			<td width="5%">&nbsp;</td>
			<td width="45%">
				<span class="customer-dt-title">REFERENCE DETAILS:</span><br><br>				
				@php
					$tid = sprintf('%04d', $invoice->tid);
					$field_name = 'Quotation No';
					$field_value = 'QT-' . $tid;
					if ($invoice->bank_id) {
						$field_name = 'Proforma No';
						$field_value = 'PI-' . $tid;
					}
				@endphp
				<b>{{ $field_name }} :</b> {{ $field_value }}<br><br>		
				<b>Date :</b> {{ dateFormat($invoice->invoicedate, 'd-M-Y') }}<br>		
				<b>Valid Till :</b> {{ dateFormat($invoice->invoiceduedate, 'd-M-Y') }} <br>
				<b>Currency :</b> Kenya Shillings <br>
				<b>Client Ref :</b> {{ $invoice->client_ref }}
			</td>
		</tr>
	</table><br>
	<table  class="ref" cellpadding="10">
		<tr><td colspan="2">Ref : <b>{{ $invoice->notes }}</b></td></tr>
	</table>
	<br>
	<table class="items" cellpadding="8">
		<thead>
			<tr>
				<td width="8%">No.</td>
				<td width="42%">ITEM DESCRIPTION</td>
				<td width="10%">QTY</td>
				<td width="10%">UoM</td>
			</tr>
		</thead>
		<tbody>
			@foreach($invoice->products as $product)
				@if ($product->a_type == 1)	
					<tr>
						<td>{{ $product->numbering }}</td>
						<td>{{ $product->product_name }}</td>
						<td class="align-c">{{ (int) $product->product_qty }}</td>
						<td class="align-c">{{ $product->unit }}</td>						
					</tr>
				@else
					<tr>
						<td><b>{{ $product->numbering }}<b></td>
						<td><b>{{ $product->product_name }}</b></td>
						@for($i = 0; $i < 2; $i++) 
							<td></td>
						@endfor
					</tr>
				@endif				
			@endforeach
			<!-- empty row with dynamic height-->
			<tr>
				<td height="{{ 340 - 30 * count($invoice->products) }}"></td>
				@for($i = 0; $i < 3; $i++) 
                    <td></td>
                @endfor
			</tr>
		</tbody>
	</table>
	<br>
	<div style="width: 100%;">
		<div style="float: left; width: 50%">
			<table class="items" cellpadding="8">
				<thead>
					<tr>
						<td width="8%">No.</td>
						<td width="42%">Skill Type</td>
						<td width="10%">Working Hours</td>
						<td width="10%">No. Technicians</td>
					</tr>
				</thead>
				<tbody>
					@php
						$budget = $invoice->budgets()->first();
					@endphp
					@isset($budget)
						@foreach ($budget->skillsets as $k => $val)
							<tr>
								<td>{{ $k+1 }}</td>
								<td>{{ $val->skill }}</td>
								<td>{{ $val->hours }}</td>
								<td>{{ $val->no_technician }}</td>
							</tr>
						@endforeach
					@endisset
				</tbody>
			</table>
		</div>
		<div style="float: left; margin-left: 5%">
			<b>Tools Required & Notes :</b><br>
			{!! $budget->tool !!}
		</div>		
	</div>	
</body>
</html>
