<html>
<head>
	<title>
		Purchase Order
	</title>
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
				<img src="{{ Storage::disk('public')->url('app/public/img/company/' . $company->logo) }}" style="object-fit:contain" width="100%"/>
			</td>
		</tr>
	</table>
	<table class="doc-table">
		<tr>
			<td class="doc-title-td">
				<span class='doc-title'>
					<b>Purchase Order</b>
				</span>				
			</td>
		</tr>
	</table><br>
	<table class="customer-dt" cellpadding="10">
		<tr>
			<td width="50%">
				<span class="customer-dt-title">SUPPLIER DETAILS:</span><br><br>
				<b>Dated :</b> {{ dateFormat($resource->date, 'd-M-Y') }}<br><br>
				<b>Name :</b> {{ $resource->supplier->name }}<br>
				<b>Address :</b> P.O Box {{ $resource->supplier->postbox }}<br>
				<b>Email :</b> {{ $resource->supplier->email }}<br>
				<b>Cell :</b> {{ $resource->supplier->phone }}
			</td>
			<td width="5%">&nbsp;</td>
			<td width="45%">
				<span class="customer-dt-title">SHIPPING DETAILS:</span><br><br>
				<b>PO Number :</b> {{ gen4tid('PO-', $resource->tid) }}<br><br>				
				<b>Branch :</b> Head Office<br>		
				<b>Client :</b> {{ $company->cname }}<br>		
				<b>Address :</b> P.O Box {{ $company->postbox }}, {{ $company->city }}<br>
				<b>Email :</b> {{ $company->email }}
			</td>
		</tr>
	</table><br>
	
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
			@foreach($resource->products as $i => $item)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="align-c">{{ +$item->qty }}</td>
					<td class="align-c">{{ $item->uom }}</td>
                    <td class="align-r">{{ numberFormat($item->rate) }}</td>
                    <td class="align-r">{{ numberFormat(+$item->qty * $item->rate) }}</td>
                </tr>
			@endforeach
			<!-- 20 dynamic empty rows -->
			@for ($i = count($resource->products); $i < 15; $i++)
				<tr>
					@for($j = 0; $j < 6; $j++) 
						<td></td>
					@endfor
				</tr>
			@endfor
			<!--  -->
			<tr>
				<td colspan="4" class="bd-t" rowspan="2"></td>
				<td class="bd align-r">Sub Total:</td>
                <td class="bd align-r">{{ numberFormat($resource->paidttl) }}</td>
			</tr>
			<tr>
                <td class="align-r">Tax {{ $resource->tax ? $resource->tax . '%' : 'Off' }}</td>
                <td class="align-r">{{ number_format($resource->grandtax, 2) }}</td>
			</tr>
			<tr>
				@php
					$user = \App\Models\Access\User\User::find($resource->user_id);
				@endphp
				<td colspan="4"><em>Approved By : </em><b>{{ !$user? $user->full_name : '' }}</b></td>
				<td class="bd align-r"><b>Grand Total:</b></td>
				<td class="bd align-r">{{ number_format($resource->grandttl, 2) }}</td>
			</tr>
		</tbody>
	</table>
</body>
</html>
