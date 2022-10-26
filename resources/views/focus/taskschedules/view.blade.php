@extends('core.layouts.app')

@section('title', 'View | Schedule Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Schedule Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                @include('focus.taskschedules.partials.taskschedule-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    

                    <div class="card-content">
                        <div class="card-body">
                            @php
                                $contract = $taskschedule->contract;
                                $details = [
                                    'Customer' => $contract->customer? $contract->customer->company : '', 
                                    'Contract' => $contract? $contract->title : '',
                                    'Schedule Title' => $taskschedule->title,
                                    'Schedule Start Date' => dateFormat($taskschedule->start_date),
                                    'Schedule End Date' => dateFormat($taskschedule->end_date),
                                    'Actual Start Date' => dateFormat($taskschedule->actual_startdate),
                                    'Actual End Date' => dateFormat($taskschedule->actual_enddate),
                                    'Service Rate' => numberFormat($taskschedule->equipments->sum('service_rate'))
                                ];
                            @endphp
                            <table class="table table-bordered table-sm mb-2">
                                @foreach ($details as $key => $val)
                                    <tr>
                                        <th width="50%">{{ $key }}</th>
                                        <td>{{ $val }}</td>
                                    </tr>
                                @endforeach
                            </table>

                            <legend>Equipments
                                <a href="#" class="btn btn-warning btn-sm mr-1" data-toggle="modal" data-target="{{ $taskschedule->equipments->count()? '#statusModal' : '' }}">
                                    <i class="fa fa-clone" aria-hidden="true"></i> Copy
                                </a>         
                            </legend>
                            <div class="table-reponsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>System ID</th>
                                            <th>Type</th>
                                            <th>Branch</th>
                                            <th>Location</th>
                                            <th>Service Rate</th>
                                        </tr>
                                    </thead>
                                    <tbody>                                                                                 
                                        @foreach ($taskschedule->equipments as $i => $row)                                            
                                            <tr>
                                                <td>{{ $i+1 }}</td>
                                                <td>{{ gen4tid('Eq-', $row->tid) }}</td>
                                                <td>{{ $row->make_type }}</td>
                                                <td>{{ $row->branch->name }}</td>
                                                <td>{{ $row->location }}</td>
                                                <td>{{ numberFormat($row->service_rate) }}</td>
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
@include('focus.taskschedules.partials.copy_modal')
@endsection
