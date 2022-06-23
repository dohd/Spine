<html>
<head>
	<title>{{ gen4tid('CN-', $resource->tid) }}</title>
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
				Cell : +254 732 345 393, +254 713 773 333<br>
				info@leanventures.co.ke<br>
				leannventures@gmail.com
			</td>
		</tr>
	</table>
	<table width="100%" style="font-size: 10pt;margin-top:5px;">
		<tr>
			<td style="text-align: center;" width="100%" class="headerData">
				<span style="font-size:15pt; color:#0f4d9b; text-transform:uppercase;"><b>Credit Note</b></span>
			</td>
		</tr>
	</table><br>
	<table width="100%" style="font-family: serif;font-size:10pt;" cellpadding="10">
		<tr>
			<td width="50%" style="border: 0.1mm solid #888888; "><span style="font-size: 7pt; color: #555555; font-family: sans;">CUSTOMER DETAILS:</span><br><br>
				<b>Client Name : </b>{{ $resource->customer->company }}<br>
				<b>Address : </b>{{ $resource->customer->address }}<br>
				<b>Email : </b>{{ $resource->customer->email }}<br>
				<b>Cell : </b> {{ $resource->customer->phone }}<br>
			<td width="5%">&nbsp;</td>
			<td width="45%" style="border: 0.1mm solid #888888;">
				<span style="font-size: 7pt; color: #555555; font-family: sans;">REFERENCE DETAILS:</span><br><br>
				<b>Credit Note No : </b> {{ gen4tid('CN-', $resource->tid) }}<br>
				<b>Invoice No : </b>{{ $resource->invoice->tid }}<br>
				<b>Date : </b>{{ dateFormat($resource->date, 'd-M-Y') }}<br>
			</td>
		</tr>
	</table><br>

	<table class="items items-table" cellpadding=8>
		<thead>
			<tr>
				<th width="6%">No</th>
				<th width="45%">Item Description</th>
				<th width="8%">Qty</th>
				<th width="10%">UoM</th>
				<th width="15%">Rate</th>
				<th width="15%">Amount</th>				
			</tr>
		</thead>
		<tbody>
            <tr class="dotted">
                <td class="mytotalss">1</td>
                <td class="mytotalss">{{ $resource->note }}</td>
                <td class="mytotalss" style="text-align: center;">1</td>
                <td class="mytotalss" style="text-align: center;">Lot</td>
                <td class="mytotalss" style="text-align: right;">{{ numberFormat($resource->subtotal) }}</td>
                <td class="mytotalss" style="text-align: right;">{{ numberFormat($resource->subtotal) }}</td>                
            </tr>
            <!-- dynamic empty rows -->
			@for ($i = 0; $i < 30; $i++)
				<tr>
					@for($j = 0; $j < 6; $j++) 
						<td></td>
					@endfor
				</tr>
			@endfor
            <tr class="dotted">
                <td colspan="4" style="border-top: solid 1px black;"></td>
                <td class="totalss" style="border: solid 1px black;">Subtotal: </td>
                <td class="totalss" style="border: solid 1px black;">{{ numberFormat($resource->subtotal) }}</td>
            </tr>
            <tr class="dotted">
                <td colspan="4"></td>
                <td class="totalss" style="border-bottom: solid 1px black;">Tax: </td>
                <td class="totalss" style="border-bottom: solid 1px black;">{{ numberFormat($resource->tax) }}</td>
            </tr>
            <tr class="dotted">
                <td colspan="4" style="border-bottom: solid 1px black;"></td>
                <td class="totalss" style="border-bottom: solid 1px black;"><b>Grand Total: </b></td>
                <td class="totalss" style="border-bottom: solid 1px black;">{{ numberFormat($resource->total) }}</td>
            </tr>
		</tbody>
	</table>
</body>
</html>
