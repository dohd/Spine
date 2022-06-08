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
                            {{ Form::open(['route' => ['biller.contractservices.update', $contractservice], 'method' => 'PATCH']) }}
                                <div class="table-reponsive">
                                    <table id="equipTbl" class="table">
                                        <thead>
                                            <tr class="bg-gradient-directional-blue white">
                                                <th>#</th>
                                                <th>Serial No</th>
                                                <th>Type</th>
                                                
                                                <th>Location</th>
                                                <th>Jobcard No</th>
                                                <th>Jobcard Date</th>

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
                                                    <td>{{ $row->equipment->location }}</td>
                                                    <td><input type="text" class="form-control" name="jobcard_no[]" value="{{ $row->jobcard_no }}" id=""></td>
                                                    <td><input type="text" class="form-control datepicker" name="jobcard_date[]" id="jobcardDate-{{ $i }}"></td>
                                                    <td>
                                                        <select name="status[]" class="form-control" id="">
                                                            @foreach (['working', 'faulty', 'cannibalised', 'decommissioned'] as $val)
                                                                <option value="{{ $val }}" {{ $val == $row->status? 'selected' : '' }}>{{ ucfirst($val) }}</option>
                                                            @endforeach
                                                        </select>                                                   
                                                    </td>
                                                    <td><input type="text" class="form-control" name="note[]" value="{{ $row->note }}" id=""></td>
                                                    <input type="hidden" name="id[]" value="{{ $row->id }}">
                                                </tr>                                                        
                                            @endforeach                                                    
                                        </tbody>
                                    </table>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-2 ml-auto">
                                        {{ Form::submit('Update', ['class' => 'btn btn-primary btn-lg']) }}
                                    </div>
                                </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
<script>  
    // initialize datepicker
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    .datepicker('setDate', new Date());

    const serviceItems = @json($contractservice->items);
    serviceItems.forEach((v, i) => {
        if (v.jobcard_date) $('#jobcardDate-'+i).datepicker('setDate', new Date(v.jobcard_date));
        else $('#jobcardDate-'+i).val(null);
    });
    
    
</script>
@endsection