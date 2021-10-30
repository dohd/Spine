@extends ('core.layouts.app')

@section ('title', 'Leads Management')

@section('page-header')
<h1>Leads Management</h1>
@endsection

@section('content')
<div class="p-2">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h4 class=" mb-0">Leads Management</h4>

        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.leads.partials.leads-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <button type="button" class="btn btn-sm btn-success font-weight-bold" data-toggle="modal" data-target="#status-modal">
                <i class="fa fa-pencil"></i> Status
            </button>
            @include('focus.leads.partials.status_modal')
        </div>

        <div class="card-body">
            <h5 class="card-title mb-1"><b>Title:</b>&nbsp;&nbsp;{{ $lead->title }}</h5>
            <table id="leads-table" class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                <tbody>
                    <tr>
                        <th>Reference</th>
                        <td>{{ $lead->reference }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        @if ($lead->status)
                            <td class='text-success'>Closed</td>
                        @else
                            <td class='font-weight-bold'>Open</td>
                        @endif
                    </tr> 
                    <tr>
                        <th>Client Name</th>
                        <td>{{ $lead->customer ? $lead->customer->name : $lead->client_name }}</td>
                    </tr>
                    <tr>
                        <th>Client Ref / Callout ID</th>
                        <td>{{ $lead->client_ref }}</td>
                    </tr>
                    @if ($lead->branch)
                        <tr>
                            <th>Client Branch</th>
                            <td>{{ $lead->branch->name }}</td>
                        </tr>
                    @endif
                    <tr>
                        <th>Client Contact</th>
                        <td>{{ $lead->customer ? $lead->customer->phone : $lead->client_contact }}</td>
                    </tr>
                    <tr>
                        <th>Client Email</th>
                        <td>{{ $lead->customer? $lead->customer->email : $lead->client_email }}</td>
                    </tr>  
                    <tr><th></th></tr>                                     
                    <tr>
                        <th>Date of Request</th>
                        <td>{{ dateFormat($lead->date_of_request) }}</td>
                    </tr>
                    <tr>
                        <th>Cost</th>
                        <td>{{ $lead->cost }}</td>
                    </tr>
                    <tr>
                        <th>Assigned to</th>
                        <td>{{ $lead->assign_to }}</td>
                    </tr>
                    <tr>
                        <th>Source</th>
                        <td>{{ $lead->source }}</td>
                    </tr>
                    <tr>
                        <th>Note</th>
                        <td>{{ $lead->note }}</td>
                    </tr>
                    <tr>
                        <th>Created at</th>
                        <td>{{ dateFormat($lead->created_at) }}</td>
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
    // default status modal select value
    $('#status').val("{{ $lead->status }}");
</script>
@endsection
