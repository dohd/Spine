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
        <h5 class="card-header">Lead</h5>
        <div class="card-body">
            <h5 class="card-title mb-1">{{ $lead->title }}</h5>
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
                        <td>{{ $lead->client_name ?: $lead->customer->name }}</td>
                    </tr>
                    <tr>
                        <th>Client Ref / Callout ID</th>
                        <td>{{ $lead->client_ref }}</td>
                    </tr>
                    @if ($lead->branch->name)
                        <tr>
                            <th>Client Branch</th>
                            <td>{{ $lead->branch->name }}</td>
                        </tr>
                    @endif
                    <tr>
                        <th>Client Contact</th>
                        <td>{{ $lead->client_contact ?: $lead->customer->phone }}</td>
                    </tr>
                    <tr>
                        <th>Client Email</th>
                        <td>{{ $lead->client_email ?: $lead->customer->email }}</td>
                    </tr>                      
                    
                    <tr><th></th></tr>                                     
                    <tr>
                        <th>Date of Request</th>
                        <td>{{ date('d-m-Y', strtotime($lead->date_of_request)) }}</td>
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
                        <td>{{ date('d-m-Y', strtotime($lead->created_at)) }}</td>
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
@endsection
