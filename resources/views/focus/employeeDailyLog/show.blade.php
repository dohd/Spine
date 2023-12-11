<!DOCTYPE html >

@extends ('core.layouts.app')

<head>
    <script src="https://cdn.tiny.cloud/1/ewcb9ttdxkr6mv3uyc8ueykuqz06aja4t3e7wuqyfqfwq17z/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
</head>


@section ('title',  'View EDL')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h3 class="mb-0">View Employee Daily Log</h2>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.employeeDailyLog.partials.edl-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card" style="border-radius: 8px;">
                    <div class="card-content">
                        <div class="card-body">


                            <h2 class="font-weight-bold mb-2">EDL</h2>

                                <div class="row">

{{--                                    <div class="col-md-4">--}}
{{--                                        <label>EDL Number:</label>--}}
{{--                                        <input type="text" readonly value="{{ $edl['edl_number'] }}" class="form-control box-size mb-2">--}}
{{--                                    </div>--}}
                                    <div class="col-md-4">
                                        <label>Employee:</label>
                                        <input type="text" readonly value="{{ $edl['employee'] }}" class="form-control box-size mb-2">
                                    </div>
                                    <div class="col-md-4">
                                        <label>Date:</label>
                                        <input type="text" readonly value="{{ $edl['date'] }}" class="form-control box-size mb-2">
                                    </div>

                                </div>

                            <h2 class="font-weight-bold mb-2 mt-2">EDL Tasks</h2>

                            @php
                                $i = 1;
                            @endphp
                            @foreach($edlTasks as $task)

                                <div class="row mb-2">

{{--                                            <div class="col-md-3">--}}
{{--                                                <label>Category:</label>--}}
{{--                                                <input type="text" readonly value="{{ $task['category'] }}" class="form-control box-size">--}}
{{--                                            </div>--}}

                                    <div class="col-12 mb-1">{{ 'Task ' . $i }}</div>

                                    <div class="col-12 col-md-8">
                                        <label>Category:</label>
                                        <input type="text" readonly value="{{ $task['subcategory'] }}" class="form-control box-size">
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label>Frequency:</label>
                                        <input type="text" readonly value="{{ $task['frequency'] }}" class="form-control box-size">
                                    </div>
                                    <div class="col-4 col-md-1 mt-1 mt-lg-0">
                                        <label>Hours:</label>
                                        <input type="text" readonly value="{{ $task['hours'] }}" class="form-control box-size">
                                    </div>
                                    <div  class="col-12 col-md-11 mt-1 mt-lg-1">
                                        <label>Description:</label>
                                        <textarea readonly class="form-control box-size descriptions" rows="3">{{ $task['description'] }}</textarea>
                                    </div>

                                </div>

                                <hr>

                                @php
                                    $i++;
                                @endphp

                            @endforeach

                            <h2 class="font-weight-bold mb-2 mt-2">EDL Remarks</h2>

                            <div class="row mb-2">

                                <div class="col-md-4">
                                    <label>Rating:</label>
                                    <input type="text" readonly value="{{ $edl['rating'] }}" class="form-control box-size">
                                </div>

                                <div class="form-group col-md-2">
                                    <label>Reviewer:</label>
                                    <input type="text" readonly value="{{ $edl['reviewer'] }}" class="form-control box-size">
                                </div>

                                <div class="form-group col-md-2">
                                    <label>Reviewed at:</label>
                                    <input type="text" readonly value="{{ $edl['reviewed_at'] }}" class="form-control box-size">
                                </div>

                                <div  class="col-md-12 mt-1">
                                    <label>Remarks:</label>
                                    <textarea id="remarks" readonly class="form-control box-size" rows="5">{{ $edl['remarks'] }}</textarea>
                                </div>

                            </div>



                            <a class="btn btn-primary" href="{{ route('biller.employee-daily-log.index') }}"> Exit </a>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script>

    tinymce.init({
        selector: '.descriptions',
        menubar: '',
        plugins: '',
        toolbar: '',
        height: 140,
        readonly  : true,
        content_style: 'body { background-color: #ECEFF1; }',
    });

    tinymce.init({
        selector: '#remarks',
        menubar: '',
        plugins: '',
        toolbar: '',
        height: 160,
        readonly  : true,
        content_style: 'body { background-color: #ECEFF1; }',
    });



    // initialize datepicker
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    $('#purchase_date').datepicker('setDate', new Date());
    $('#warranty_expiry_date').datepicker('setDate', new Date());
</script>
@endsection