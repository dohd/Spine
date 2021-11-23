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
		.dotted td {
			border-bottom: dotted 1px black;
		}
		.dottedt th {
			border-bottom: dotted 1px black;
		}
		h5 {
			text-decoration: underline;
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
			<td width="60%" align="left" style="color:#0f4d9b;font-size:8pt;">
				<img src="{{ Storage::disk('public')->url('app/public/img/company/ico/logo.jpg') }}" style="width:350px;" /><br><span style="color:#0f4d9b;font-size:9pt;"> Supply, Installation, Maintenance & Repair of:</span><br />
				<img src="{{ Storage::disk('public')->url('app/public/img/company/ico/bullets.png') }} " style="width:8px;" /> Air Conditioners & Refrigerators &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <img src="{{ Storage::disk('public')->url('app/public/img/company/ico/bullets.png') }}" style="width:8px;margin-left:6px;" /> Laboratory Fume Cupboards <br>
				<img src="{{ Storage::disk('public')->url('app/public/img/company/ico/bullets.png') }}" style="width:8px;" /> Coldrooms & Chillers&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <img src="{{ Storage::disk('public')->url('app/public/img/company/ico/bullets.png') }}" style="width:8px;margin-left:62px;" /> Steam Bath and Saunas<br />
				<img src="{{ Storage::disk('public')->url('app/public/img/company/ico/bullets.png') }}" style="width:8px;" /> Mechanical Ventilation Systems &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <img src="{{ Storage::disk('public')->url('app/public/img/company/ico/bullets.png') }}" style="width:8px; " /> Raised Floors / Access Panels <br /> <br />
				<span style="color:#0f4d9b;font-size:9pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...&nbsp;&nbsp;&nbsp;and General Suppliers</span>
			</td>
			<td width="40%" align="right" style="color:#0f4d9b;font-size:10pt; ">
				<br><br>
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
			<td style="text-align: center;" width="100%" class="headerData"><span style="font-size:15pt;color:#0f4d9b;text-transform: uppercase;"><b>DIAGNOSIS REPORT</b></span>
			</td>
		</tr>
	</table><br>
	<table width="100%" style="font-family: serif;font-size:10pt;" cellpadding="10">
		<tr>
			<td width="50%" style="border: 0.1mm solid #888888; "><span style="font-size: 7pt; color: #555555; font-family: sans;">CUSTOMER DETAILS:</span><br /><br />
				<b>Client Name :</b> {{$invoice->client->company}}<br />
				<b>Branch Name : </b>{{$invoice->branch->name}}<br />
				<b>Region : </b> {{$invoice->region}}<br />
				<b>Attention :</b> {{$invoice['attention']}}<br />
			<td width="5%">&nbsp;</td>
			<td width="45%" style="border: 0.1mm solid #888888;"><span style="font-size: 7pt; color: #555555; font-family: sans;">REFERENCE DETAILS:</span><br /><br />
				<b>Report No :</b> {{$invoice['tid']}}<br />
				<b>Date :</b> {{dateFormat($invoice['report_date'],$company['main_date_format'])}}<br />
				<b>Client Ref No :</b> {{$invoice->reference}}<br />
				<b>Prepared By : </b>{{$invoice->prepared_by}} <br />
			</td>
		</tr>
	</table><br />
	<table width="100%" style="font-family: serif;font-size:10pt;" cellpadding="10">
		<tr>
			<td style="border: 0.1mm solid #888888; ">
				Ref : <b>{{$invoice->subject}}</b>
			</td>
		</tr>
	</table>
	<br>
	<table class="items" width="100%" style="font-size: 10pt; border-collapse: collapse;height: 700px;" cellpadding="8">
		<thead>
			<tr>
				<td width="10%">Tag No</td>
				<td width="15%">Job Card</td>
				<td width="15%">Make</td>
				<td width="15%">Capacity</td>
				<td width="15%">Location</td>
				<td width="15%">Last Service Date</td>
				<td width="15%">Next Service Date</td>
			</tr>
		</thead>
		<tbody>
			<!-- ITEMS HERE -->
			@foreach($invoice->items as $item)
			<tr class="dotted">
				<td class="mytotalss">{{$item->tag_number}}</td>
				<td class="mytotalss">{{$item->joc_card}}</td>
				<td class="mytotalss">{{$item->make}}</td>
				<td class="mytotalss">{{$item->capacity}}</td>
				<td class="mytotalss">{{$item->location}}</td>
				<td class="mytotalss">{{dateFormat($item->last_service_date,$company['main_date_format'])}}</td>
				<td class="mytotalss">{{dateFormat($item->next_service_date,$company['main_date_format'])}}</td>
			</tr>
			@endforeach
			<!-- END ITEMS HERE -->
		</tbody>
	</table><br>
	<i align="center">Work was attended by the following technician(s) : <b>{{$invoice->technician}}</b> </i>
	<div>
		<h5>Call Description</h5>
		<p>{{$invoice->lead->note}} </p>
	</div>
	<div>
		<h5> Findings & Root Cause</h5>
		<p>{!! $invoice['root_cause'] !!}</p>
	</div>
	<div>
		<h5>Action Taken</h5>
		<p>{!! $invoice['action_taken'] !!}</p>
	</div>
	<div>
		<h5>Recommendation</h5>
		<p>{!! $invoice['recommendations'] !!}</p>
	</div>
	<br>
	<table class="items" width="100%" style="font-size: 10pt; border-collapse: collapse;height: 700px;" cellpadding="8">
		<tr>
			<th colspan="4">PICTORIAL</th>
		</tr>
		<tr class="dottedt">
			<th width="25%"></th>
			<th width="25%"></th>
			<th width="25%"></th>
			<th width="25%"></th>
		</tr>
		<tr class="dotted">
			<td>
				@if(isset($invoice->image_one))
					<img src="{{ Storage::disk('public')->url('app/public/img/djcreport/' . $invoice['image_one']) }}" alt="" border=3 height=200 width=300></img>
				@endif
			</th>
			<td>
				@if(isset($invoice->image_two))
					<img src="{{ Storage::disk('public')->url('app/public/img/djcreport/' . $invoice['image_two']) }}" alt="" border=3 height=200 width=300></img>
				@endif</th>
				<!-- considering it is on the same folder that .html file -->
			<td>
				@if(isset($invoice->image_three))
					<img src="{{ Storage::disk('public')->url('app/public/img/djcreport/' . $invoice['image_three']) }}" alt="" border=3 height=200 width=300></img>
				@endif
			</th>
			<td>
				@if(isset($invoice->image_four))
					<img src="{{ Storage::disk('public')->url('app/public/img/djcreport/' . $invoice['image_four']) }}" alt="" border=3 height=200 width=300></img>
				@endif
			</th>
		</tr>
		<tr>
			<td class="cost">{{$invoice->caption_one}}</th>
			<td class="cost">{{$invoice->caption_two}}</th>
			<td class="cost">{{$invoice->caption_three}}</th>
			<td class="cost">{{$invoice->caption_four}}</th>
		</tr>
	</table>
</body>
</html>
