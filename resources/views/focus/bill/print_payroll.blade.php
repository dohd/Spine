<html>

<head>
    <title>
        @php
            
            $tid = $resource->employee_id;
           
            $tid = gen4tid('EMP', $tid);
        @endphp
        PaySlip
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

        .table-taxable {
            width: 98%;
            margin: .5rem;
        }

        .border {
            border: 1px solid black;
            border-collapse: collapse;
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
                <img src="{{ Storage::disk('public')->url('app/public/img/company/' . $company->logo) }}"
                    style="object-fit:contain" width="100%" />
            </td>
        </tr>
    </table>
    <table class="doc-table">
        <tr>
            <td class="doc-title-td">
                <span class='doc-title'>
                    <b>PAYSLIP</b>
                </span>
            </td>
        </tr>
    </table><br>
    <table class="customer-dt" cellpadding="10">
        <tr>
            <td width="50%">
                <span class="customer-dt-title">EMPLOYEE DETAILS:</span><br><br>
               
                <b>Employee No :</b> EMP-200<br>
                <b>KRA PIN :</b> A0WADAD2154<br>
                <b>Contract Expiry Date :</b> 24/05/2023<br>
                <b>Employee Name :</b> Arnold<br>
                <b>Job Title :</b> Developer<br>
                <b>Department :</b> Technical<br>
            </td>
            <td width="5%">&nbsp;</td>
            <td width="45%">
                <span class="customer-dt-title">PAYSLIP DETAILS:</span><br><br>
                <b>Basic Pay :</b> 50000<br><br>
                <b>Taxable Gross Allowances :</b>30000<br>
                <b>NSSF :</b> 1250 <br>
                <b>NHIF :</b> 1050 <br>
                <b>PAYE :</b> 10500 <br>
                <b>Non-Taxable Gross Allowances :</b> 10500 <br>
                <b>Net Pay :</b> 10500 <br>
            </td>
        </tr>
    </table><br>
    <p><b>Taxable Allowances and Deductions</b></p>
    <table class="border" style="width:100%">
        <thead>
            <tr>
                <td class="border"  width="50%">
                    Allowances
                </td>
                <td class="border" width="50%">
                    Deductions
                </td>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td class="border"  width="50%">
                    <table class="table-taxable border" cellpadding="8">
                        <thead>
                            <tr >
                                <th class="border">Item</th>
                                <th class="border">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border">
                                    Transport
                                </td>
                                <td class="border">
                                    10000
                                </td>
                            </tr>
                            <tr>
                                <td class="border">
                                    Housing
                                </td>
                                <td class="border">
                                    10000
                                </td>
                            </tr>
                            <tr>
                                <td class="border">
                                    Other
                                </td>
                                <td class="border">
                                    10000
                                </td>
                            </tr>
                            <tr>
                                <td class="border">
                                    <b>Total</b>
                                </td>
                                <td class="border">
                                    30000
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td class="border" width="50%">
                    <table class="table-taxable border" cellpadding="8">
                        <thead>
                            <tr>
                                <th class="border">Item</th>
                                <th class="border">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border">
                                    NSSF
                                </td>
                                <td class="border">
                                    1000
                                </td>
                            </tr>

                            <tr>
                                <td class="border">
                                    Other
                                </td>
                                <td class="border">
                                    10000
                                </td>
                            </tr>
                            <tr>
                                <td class="border">
                                    <b>Total</b>
                                </td>
                                <td class="border">
                                    11000
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>

        </tbody>
    </table>
    <hr>
    <h3>Gross Taxable Allowance : <b>15000</b></h3>
    <hr>
    <p><b>Non-Taxable Allowances and Deductions</b></p>
    <table class="border" style="width:100%">
        <thead>
            <tr>
                <td class="border"  width="50%">
                    Allowances
                </td>
                <td class="border" width="50%">
                    Deductions
                </td>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td class="border"  width="50%">
                    <table class="table-taxable border" cellpadding="8">
                        <thead>
                            <tr >
                                <th class="border">Item</th>
                                <th class="border">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border">
                                    Benefits
                                </td>
                                <td class="border">
                                    10000
                                </td>
                            </tr>
                           
                            <tr>
                                <td class="border">
                                    Other Allowances
                                </td>
                                <td class="border">
                                    10000
                                </td>
                            </tr>
                            <tr>
                                <td class="border">
                                    <b>Total</b>
                                </td>
                                <td class="border">
                                    30000
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td class="border" width="50%">
                    <table class="table-taxable border" cellpadding="8">
                        <thead>
                            <tr>
                                <th class="border">Item</th>
                                <th class="border">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border">
                                    Loan
                                </td>
                                <td class="border">
                                    1000
                                </td>
                            </tr>
                            <tr>
                                <td class="border">
                                    Advance
                                </td>
                                <td class="border">
                                    1000
                                </td>
                            </tr>
                            <tr>
                                <td class="border">
                                    NHIF
                                </td>
                                <td class="border">
                                    1000
                                </td>
                            </tr>

                            <tr>
                                <td class="border">
                                    Other
                                </td>
                                <td class="border">
                                    10000
                                </td>
                            </tr>
                            <tr>
                                <td class="border">
                                    <b>Total</b>
                                </td>
                                <td class="border">
                                    11000
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>

        </tbody>
    </table>
    <hr>
    <h3>Gross Non-Taxable Allowance : <b>15000</b></h3>
    <hr>









    </div>
    <br>
    <div>{!! $resource->extra_footer !!}</div>
</body>

</html>
