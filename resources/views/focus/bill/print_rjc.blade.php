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
		.dottedt th {
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
</head>
<body>
	<htmlpagefooter name="myfooter">
		<div class="footer">Page {PAGENO} of {nb}</div>
	</htmlpagefooter>
	<sethtmlpagefooter name="myfooter" value="on" />
	
	<table width="100%" style="border-bottom: 0.8mm solid #0f4d9b;">
		<tr>
			<td width="60%" align="left" style="color:#0f4d9b;font-size:8pt;">
				<img src="{{ Storage::disk('public')->url('app/public/img/company/ico/logo.jpg') }}" style="width:350px;" /><br><span style="color:#0f4d9b;font-size:9pt;"> Supply, Installation, Maintenance & Repair of:</span><br>
				<img src="{{ Storage::disk('public')->url('app/public/img/company/ico/bullets.png') }}" style="width:8px;" /> Air Conditioners & Refrigerators &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <img src="{{ Storage::disk('public')->url('app/public/img/company/ico/bullets.png') }}" style="width:8px;margin-left:6px;" /> Laboratory Fume Cupboards <br>
				<img src="{{ Storage::disk('public')->url('app/public/img/company/ico/bullets.png') }}" style="width:8px;" /> Coldrooms & Chillers&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <img src="{{ Storage::disk('public')->url('app/public/img/company/ico/bullets.png') }}" style="width:8px;margin-left:62px;" /> Steam Bath and Saunas<br>
				<img src="{{ Storage::disk('public')->url('app/public/img/company/ico/bullets.png') }}" style="width:8px;" /> Mechanical Ventilation Systems &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <img src="{{ Storage::disk('public')->url('app/public/img/company/ico/bullets.png') }}" style="width:8px; " /> Raised Floors / Access Panels <br> <br>
				<span style="color:#0f4d9b;font-size:9pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...&nbsp;&nbsp;&nbsp;and General Suppliers</span>
			</td>
			<td width="40%" align="right" style="color:#0f4d9b;font-size:10pt; ">
				<br><br>
				Lean Aircons Building, Opp NextGen Mall<br>
				Mombasa Road, Nairobi - Kenya<br>
				P.O Box 36082 - 00200.<br>
				Cell : +254 732 345 393, +254 787 391 015<br>
				info@leanventures.co.ke<br>
				leannventures@gmail.com
			</td>
		</tr>
	</table>
	<table width="100%" style="font-size: 10pt;margin-top:5px;">
		<tr>
			<td style="text-align: center;" width="100%" class="headerData">
				<span style="font-size:15pt;color:#0f4d9b;"><b>REPAIR / CORRECTIVE ACTION REPORT</b></span>
			</td>
		</tr>
	</table><br>
	<table width="100%" style="font-family: serif;font-size:10pt;" cellpadding="10">
		<tr>
			<td width="50%" style="border: 0.1mm solid #888888; "><span style="font-size: 7pt; color: #555555; font-family: sans;">CUSTOMER DETAILS:</span><br><br>
				<b>Client Name : </b>{{ '' }}<br>
				<b>Site / Branch Name : </b>{{ '' }}<br>
				<b>Region : </b>{{ $invoice->region }}<br>
				<b>Attention : </b> {{ $invoice->attention }}<br>
			<td width="5%">&nbsp;</td>
			<td width="45%" style="border: 0.1mm solid #888888;">
				<span style="font-size: 7pt; color: #555555; font-family: sans;">REFERENCE DETAILS:</span><br><br>
				<b>Report No : </b> {{ 'DjR-'.sprintf('%04d', $invoice->tid) }}<br>
				<b>Date : </b>{{ dateFormat($invoice->report_date, 'd-M-Y') }}<br><br>
				<b>Prepared By : </b>{{ $invoice->prepared_by }}<br>
			</td>
		</tr>
	</table><br>
	<table width="100%" style="font-family: serif;font-size:10pt;" cellpadding="10">
		<tr>
			<td style="border: 0.1mm solid #888888;">
				Ref : <b>{{ '' }}</b>
			</td>
		</tr>
	</table>
	<h5><span>a.</span> Equipment Details</h5>
	<table class="items items-table" cellpadding=8>
		<thead>
			<tr>
				<th width="10%">Tag No</th>
				<th width="15%">Type</th>
				<th width="15%">Make</th>
				<th width="15%">Capacity</th>
				<th width="15%">Location</th>
				<th width="15%">Last Service Date</th>
				<th width="15%">Next Service Date</th>
			</tr>
		</thead>
		<tbody>
			<!-- ITEMS HERE -->
			@foreach($invoice->rjc_items as $item)
				<tr class="dotted">
					<td class="mytotalss">{{ $item->tag_number }}</td>
					<td class="mytotalss">{{ $item->equipment_type }}</td>
					<td class="mytotalss">{{ $item->make }}</td>
					<td class="mytotalss">{{ $item->capacity }}</td>
					<td class="mytotalss">{{ $item->location }}</td>
					<td class="mytotalss">{{ dateFormat($item->last_service_date, 'd-m-Y') }}</td>
					<td class="mytotalss">{{ dateFormat($item->next_service_date, 'd-m-Y') }}</td>
				</tr>
			@endforeach
			<!-- END ITEMS HERE -->
		</tbody>
	</table>
	<div>
		<h5><span>b.</span> Call Out Details</h5>
		<p>
			{{ '' }} <b>on</b> <i>{{ '' }}</i> <b>as
			per reference</b> <i>{{ '' }}</i>
		</p><br>
		<table class="items items-table" cellpadding=8>
			<thead>
				<tr>
					<th width="30%" >Djc Number</th>
					<th width="30%">Djc Date</th>
					<th width="40%">Diagnosis Technician(s)</th>
				</tr>
			</thead>
			<tbody>
				<tr class="dotted">
					<td>{{ $invoice->job_card }}</td>
					<td>{{ dateFormat($invoice->report_date, 'd-M-Y') }}</td>
					<td>{{ $invoice->technician }}</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div>
		<h5><span>c.</span> Findings & Root Cause</h5>
		<p>{!! $invoice->root_cause !!}</p>
	</div>
	<div>
		<h5><span>d.</span> Action Taken</h5>
		<p>{!! $invoice->action_taken !!}</p>
	</div>
	<div>
		<h5><span>e.</span> Recommendation</h5>
		<p>{!! $invoice->recommendations !!}</p>
	</div>
	<h5><span>f.</span> Pictorials</h5>
	@if(isset($invoice->image_one) || isset($invoice->image_two) || isset($invoice->image_three) || isset($invoice->image_four))
		<table class="items items-table" cellpadding="8">		
			<tr class="dottedt">
				<th width="25%"></th>
				<th width="25%"></th>
				<th width="25%"></th>
				<th width="25%"></th>
			</tr>
			<tr class="dotted">
				<td>
					@isset($invoice->image_one)
						<img src="{{ Storage::disk('public')->url('app/public/img/djcreport/' . $invoice->image_one) }}" alt="" border=3 height=200 width=300></img>
					@endisset
				</td>
				<td>
					@isset($invoice->image_two)
						<img src="{{ Storage::disk('public')->url('app/public/img/djcreport/' . $invoice->image_two) }}" alt="" border=3 height=200 width=300></img>
					@endisset
				</td>
				<td>
					@isset($invoice->image_three)
						<img src="{{ Storage::disk('public')->url('app/public/img/djcreport/' . $invoice->image_three) }}" alt="" border=3 height=200 width=300></img>
					@endisset
				</td>
				<td>
					@isset($invoice->image_four)
						<img src="{{ Storage::disk('public')->url('app/public/img/djcreport/' . $invoice->image_four) }}" alt="" border=3 height=200 width=300></img>
					@endisset
				</td>
			</tr>
			<tr>
				<td class="cost">{{ $invoice->caption_one }}</td>
				<td class="cost">{{ $invoice->caption_two }}</td>
				<td class="cost">{{ $invoice->caption_three }}</td>
				<td class="cost">{{ $invoice->caption_four }}</td>
			</tr>
		</table>
	@endif
</body>
</html>
