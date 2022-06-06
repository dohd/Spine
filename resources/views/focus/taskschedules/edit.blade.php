@extends('core.layouts.app')

@section('title', 'Edit | Task Schedule Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Task Schedule Management</h4>
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
                            {{ Form::model($taskschedule, ['route' => ['biller.taskschedules.update', $taskschedule], 'method' => 'PATCH']) }}
                                <div class="form-group row">
                                    <div class="col-3">
                                        <label for="title">Schedule Title</label>
                                        {{ Form::text('title', null, ['class' => 'form-control', 'required']) }}
                                    </div>
                                    <div class="col-3">
                                        <label for="start_date">Start Date</label>
                                        {{ Form::text('start_date', null, ['class' => 'form-control datepicker', 'id' => 'start_date']) }}
                                    </div>
                                    <div class="col-3">
                                        <label for="end_date">End Date</label>
                                        {{ Form::text('end_date', null, ['class' => 'form-control datepicker', 'id' => 'end_date']) }}
                                    </div>                                    
                                </div>
                                <legend>Equipments</legend>
                                <div class="table-reponsive mb-2">
                                    <table id="equipmentTbl" class="table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Serial No</th>
                                                <th>Type</th>
                                                <th>Branch</th>
                                                <th>Location</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>                                                                                    
                                            @foreach ($taskschedule->taskschedule_equipments as $i => $row)                                            
                                                <tr>
                                                    <td>{{ $i+1 }}</td>
                                                    <td>{{ $row->equipment->unique_id }}</td>
                                                    <td>{{ $row->equipment->make_type }}</td>
                                                    <td>{{ $row->equipment->branch->name }}</td>
                                                    <td>{{ $row->equipment->location }}</td>
                                                    <td>
                                                        <button type="button" class="btn btn-outline-light btn-sm remove">
                                                            <i class="fa fa-trash fa-lg text-danger"></i>
                                                        </button>
                                                    </td>
                                                    <input type="hidden" name="id[]" value="{{ $row->id }}">
                                                </tr>                                                        
                                            @endforeach                                                    
                                        </tbody>
                                    </table>                                    
                                </div>
                                <div class="form-group row">
                                    <div class="col-11">
                                        {{ Form::submit('Update', ['class' => 'btn btn-primary float-right btn-lg']) }}
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

@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script>
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});

    const schedule = @json($taskschedule);
    $('.datepicker')
    .datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true}) 
    $('#start_date').datepicker('setDate', new Date(schedule.start_date));
    $('#end_date').datepicker('setDate', new Date(schedule.end_date));     
    
    // remove equipmentTbl row
    $('#equipmentTbl').on('click', '.remove', function() {
        $(this).parents('tr').remove();
    });

</script>
@endsection