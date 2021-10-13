@extends ('core.layouts.app')

@section ('title', 'Leads Management')

@section('page-header')
<h1>Leads Management</h1>
@endsection

@section('content')
<div class="p-2">
    <div class="card">
        <h5 class="card-header">Lead</h5>
        <div class="card-body">
            <h5 class="card-title mb-1">{{ $lead->title }}</h5>
            <table id="leads-table" class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                <tbody>
                    <tr>
                        <td>Reference</td>
                        <th>{{ $lead->reference }}</th>
                    </tr>
                    <tr>
                        <td>Status</td>
                        @if ($lead->status)
                            <th class='text-success'>Closed</th>
                        @else
                            <th>Open</th>
                        @endif
                    </tr>                    
                    <tr>
                        <td>Client Name</td>
                        <th>{{ $lead->client_name ?: $customer->name }}</th>
                    </tr>
                    @if ($branch->name)
                        <tr>
                            <td>Client Branch</td>
                            <th>{{ $branch->name }}</th>
                        </tr>
                    @endif
                    <tr>
                        <td>Client Contact</td>
                        <th>{{ $lead->client_contact ?: $customer->phone }}</th>
                    </tr>
                    <tr>
                        <td>Client Email</td>
                        <th>{{ $lead->client_email ?: $customer->email }}</th>
                    </tr>                    
                    <tr>
                        <td>Date of Request</td>
                        <th>{{ $lead->date_of_request }}</th>
                    </tr>
                    <tr>
                        <td>Cost</td>
                        <th>{{ $lead->cost }}</th>
                    </tr>
                    <tr>
                        <td>Assigned to</td>
                        <th>{{ $lead->assign_to }}</th>
                    </tr>
                    <tr>
                        <td>Source</td>
                        <th>{{ $lead->source }}</th>
                    </tr>
                    <tr>
                        <td>Note</td>
                        <th>{{ $lead->note }}</th>
                    </tr>
                    <tr>
                        <td>Created at</td>
                        <th>{{ date('d-m-Y', strtotime($lead->created_at)) }}</th>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection


@section('after-scripts')
{{-- For DataTables --}}
{{ Html::script(mix('js/dataTable.js')) }}

<script>

</script>
@endsection