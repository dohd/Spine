<html>
<head>
	@php
		$tid = sprintf('%04d', $resource->tid);
		$v_no =  ' (v' . $resource->verified_jcs[0]->verify_no . ')';
		$field_value = 'QT-' . $tid;
		if ($resource->bank_id) $field_value = 'PI-' . $tid;
	@endphp
	<title>{{ $field_value . $v_no }}</title>
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
		<div class="footer">Page {PAGENO} of {nb}</div>
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
                <span class='doc-title'><b>WORKDONE VERIFICATION</b></span>			
			</td>
		</tr>
	</table><br>
	<table class="customer-dt" cellpadding="10">
		<tr>
			<td width="50%">
				<span class="customer-dt-title">CUSTOMER DETAILS:</span><br><br>
				<b>Client Name :</b> {{ $resource->client->company }}<br>
				<b>Branch :</b> {{ $resource->branch->name }}<br>
				<b>Address :</b> {{ $resource->client->address }}<br>
				<b>Email :</b> {{ $resource->client->email }}<br>
				<b>Cell :</b> {{ $resource->client->phone }}<br>
				<b>Attention :</b> {{ $resource->attention }}<br>
			</td>
			<td width="5%">&nbsp;</td>
			<td width="45%">
				<span class="customer-dt-title">REFERENCE DETAILS:</span><br><br>
				@php
					$tid = sprintf('%04d', $resource->tid);
					$v_no =  ' (v' . $resource->verified_jcs[0]->verify_no . ')';
					$field_value = 'QT-' . $tid;
					if ($resource->bank_id) $field_value = 'PI-' . $tid;
				@endphp
				<b>Reference No :</b> {{ $field_value . $v_no }}<br>
                <b>Reference Date :</b> {{ dateFormat($resource->verification_date, 'd-M-Y') }} <br>
				<b>Currency :</b> Kenya Shillings <br><br>
				<b>Client Ref: </b> {{ $resource->client_ref }}
			</td>
		</tr>
	</table><br>
	<table  class="ref" cellpadding="10">
		<tr><td colspan="2">Ref : <b>{{ $resource->notes }}</b></td></tr>
        <tr>
            @php
				$jcard_refs = array();
                $dnote_refs = array();
                foreach ($resource->verified_jcs as $jc) {
                    if ($jc->type == 2) $dnote_refs[] = $jc->reference;
                    else $jcard_refs[] = $jc->reference;
                }                
            @endphp
            <td>RJC : <b>{{ implode(', ', $jcard_refs) }}</b></td>
            <td>DNOTE : <b>{{ implode(', ', $dnote_refs) }}</b></td>
        </tr>		
	</table>
	<br>
	<table class="items" style="border-bottom: none;" cellpadding="8">
		<thead>
			<tr>
				<td width="6%">No.</td>
				<td width="38%">ITEM DESCRIPTION</td>
				<td width="6%">QTY</td>
				<td width="10%">UoM</td>
				<td width="15%">RATE</td>
				<td width="15%">AMOUNT</td>
                <td width="10%">REMARK</td>
			</tr>
		</thead>
		<tbody>
			@foreach($resource->verified_items as $product)
				@if ($product->a_type == 1)	
					
					<tr>
						<td>{{ $product->numbering }}</td>
						<td>{{ $product->product_name }}</td>
						<td class="align-c">{{ (int) $product->product_qty }}</td>
						<td class="align-c">{{ $product->unit }}</td>
                        <td class="align-r">
                            @if ($resource->print_type == 'inclusive')
                                {{ number_format($product->product_subtotal, 2) }}
                            @else
                                {{ number_format($product->product_price, 2) }}
                            @endif
                        </td>
                        <td class="align-r">
							@php
								$product_qty = (int) $product->product_qty;
							@endphp
                            @if ($resource->print_type == 'inclusive')
                                {{ number_format($product_qty * $product->product_subtotal, 2) }}
                            @else
                                {{ number_format($product_qty * $product->product_price, 2) }}
                            @endif
                        </td>						
                        <td>{{ $product->remark }}</td>
					</tr>
				@else
					<tr>
						<td><b>{{ $product->numbering }}<b></td>
						<td><b>{{ $product->product_name }}</b></td>
						@for($i = 0; $i < 5; $i++) 
                            <td></td>
                        @endfor
					</tr>
				@endif				
			@endforeach
			<!-- 20 dynamic empty rows -->
			@for ($i = count($resource->verified_items); $i < 15; $i++)
				<tr>
					@for($j = 0; $j < 7; $j++) 
						<td></td>
					@endfor
				</tr>
			@endfor
			<!--  -->
			<tr>
				<td colspan="4" class="bd-t">
					@if ($resource->bank_id)
						<span class="customer-dt-title">BANK DETAILS:</span><br>
						<b>Account Name :</b> Lean Ventures Limited<br>
						<b>Account Number :</b> 1267496231<br>
						<b>Bank :</b> KCB &nbsp;&nbsp;<b>Branch :</b> Nextgen Mall <br>
						<b>Currency :</b> Kenya Shillings &nbsp;&nbsp;<b>Swift Code :</b> KCBLKENX <br>
						(KCB Mpesa Paybill: 522 522)
					@endif
				</td>
				<td class="bd align-r">Sub Total:</td>
                <td class="bd align-r">
                    @if ($resource->print_type == 'inclusive')
                        {{ number_format($resource->total, 2) }}
                    @else
                        {{ number_format($resource->subtotal, 2) }}
                    @endif
                </td>				
                <td class="bd-t"></td>
			</tr>
			<tr>
				<td colspan="4">
					@isset($resource->gen_remark)
						<b>General Remark</b> : <i>{{ $resource->gen_remark }}<i>
					@endisset
				</td>
				@if ($resource->print_type == 'inclusive')
					<td class="align-r">VAT {{ $resource->tax_id }}%</td>
					<td class="align-r">
						@php
							$text = $resource->tax_id ? 'INCLUSIVE' : 'NONE';
						@endphp
						{{ $text }}
					</td>
				@else
					<td class="align-r">Tax {{ $resource->tax_id }}%</td>
					<td class="align-r">{{ number_format($resource->tax, 2) }}</td>
				@endif
				<td></td>
			</tr>
			<tr>
				<td colspan="4" style="border-bottom: 1px solid;"><em>Prepared By : </em><b>{{ $resource->prepared_by }}</b></td>
				<td class="bd align-r"><b>Grand Total:</b></td>
				<td class="bd align-r">{{ number_format($resource->total, 2) }}</td>
				<td style="border-bottom: 1px solid;"></td>
			</tr>
		</tbody>
	</table>
</body>
</html>
