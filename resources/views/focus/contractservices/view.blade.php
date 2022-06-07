@extends('core.layouts.app')

@section('title', 'View | Contract Service Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Contract Service Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.contractservices.partials.contractservices-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <table id="serviceTbl" class="table table-bordered table-sm mb-2">
                                @php
                                    $details = [
                                        'Contract' => $contractservice->contract->title,
                                        'Task Schedule' => $contractservice->task_schedule->title,
                                        'Service Name' => $contractservice->name,
                                        'Amount' => numberFormat($contractservice->amount),
                                    ];
                                @endphp
                                @foreach ($details as $key => $val)
                                    <tr>
                                        <th>{{ $key }}</th>
                                        <td>{{ $val }}</td>
                                    </tr>
                                @endforeach
                            </table>
                            <div class="table-reponsive">
                                <table class="table">
                                    <thead>
                                        <tr class="bg-gradient-directional-blue white">
                                            <th>#</th>
                                            <th>Serial No</th>
                                            <th>Type</th>
                                            <th>Branch</th>
                                            <th>Location</th>
                                            <th>Jobcard</th>
                                            <th>Jobcard Date</th>
                                            <th>Last Service Date</th>
                                            <th>Next Service Date</th>
                                            <th>Status</th>
                                            <th>Note</th>
                                        </tr>
                                    </thead>
                                    <tbody>                                            
                                        @foreach ($contractservice->items as $i => $row)                                            
                                            <tr>
                                                <td>{{ $i+1 }}</td>
                                                <td>{{ $row->equipment->unique_id }}</td>
                                                <td>{{ $row->equipment->make_type }}</td>
                                                <td>{{ $row->equipment->branch->name }}</td>
                                                <td>{{ $row->equipment->location }}</td>
                                                <td>{{ $row->jobcard_no }}</td>
                                                <td>{{ $row->jobcard_date?  dateFormat($row->jobcard_date) : '' }}</td>
                                                <td>{{ $row->last_service_date?  dateFormat($row->last_service_date) : '' }}</td>
                                                <td>{{ $row->next_service_date?  dateFormat($row->next_service_date) : '' }}</td>
                                                <td>{{ $row->status }}</td>
                                                <td>{{ $row->note }}</td>
                                            </tr>                                                        
                                        @endforeach                                                    
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection