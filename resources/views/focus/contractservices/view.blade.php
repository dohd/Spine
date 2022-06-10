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
                            <div class="table-reponsive" style="overflow-x: scroll;">
                                <table class="table">
                                    <thead>
                                        <tr class="bg-gradient-directional-blue white">
                                            <th>System ID</th>
                                            <th>Location</th>
                                            <th>Description</th>                                           
                                            <th>Jobcard No</th>
                                            <th>Jobcard Date</th>
                                            <th width="10%">Status</th>
                                            <th>Amount</th>
                                            <th>Charge</th>                                            
                                            <th>Technician</th>
                                            <th width="12%">Note</th>
                                        </tr>
                                    </thead>
                                    <tbody>                                            
                                        @foreach ($contractservice->items as $i => $row)                                            
                                            <tr>                                                
                                                <td>{{ gen4tid('E-', $row->equipment->tid) }}</td>
                                                <td>{{ $row->equipment->location }}</td>  
                                                <td>
                                                    @php
                                                        $descr = array_intersect_key(
                                                            $row->equipment->toArray(), 
                                                            array_flip(['make_type', 'equip_serial', 'unique_id', 'capacity', 'machine_gas'])
                                                        );
                                                        echo implode('; ', array_values($descr));
                                                    @endphp                                                                                          
                                                </td>                                                
                                                <td>{{ $row->jobcard_no }}</td>
                                                <td>{{ $row->jobcard_date?  dateFormat($row->jobcard_date) : '' }}</td>
                                                <td>{{ ucfirst($row->status) }}</td>
                                                <td>{{ numberFormat($row->equipment->service_rate) }}</td>
                                                <td>
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input ml-1"  {{ $row->is_charged ? 'checked' : '' }} onClick="return false;">
                                                    </div>
                                                </td>  
                                                <td>{{ $row->technician }}</td>
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