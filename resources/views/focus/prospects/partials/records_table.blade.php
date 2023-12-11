<table id="records_table" class="table p-1 table-striped table-bordered recordsTable">
    <thead>
        <tr>
            <td>CURRENT ERP</td>
            <td>ERP USAGE</td>
            <td>HAS CHALLENGES</td>
            <td>ERP CHALLENGES</td>
            <td>DEMO DATE</td>
            <td>REMARKS</td>
        </tr>
    </thead>

    <tbody>

        @if ($prospectrecord)
               
        <tr>
            <td> {{ $prospectrecord->current_erp }} </td>
            <td> {{ $prospectrecord->current_erp_usage }} </td>
            <td> {{ $prospectrecord->erp_challenges == "1" ?'Yes':'No' }} </td>
            <td> {{ $prospectrecord->current_erp_challenges }} </td>
            <td> {{ $prospectrecord->reminder_date }} </td>
            <td> {{ $prospectrecord->any_remarks }} </td>
        </tr>
   
@else
    <tr>
        <td style="text-align: center; vertical-align: middle;" colspan="5"> No record found </td>
    </tr>
@endif

    </tbody>
</table>
