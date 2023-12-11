<!DOCTYPE html >

<head>
    <script src="https://cdn.tiny.cloud/1/ewcb9ttdxkr6mv3uyc8ueykuqz06aja4t3e7wuqyfqfwq17z/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
</head>

@extends ('core.layouts.app')

@section ('title',  'Employee Daily Log')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h2 class="mb-0">Edit Employee Daily Log</h2>
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

                            {{ Form::open(['route' => ['biller.employee-daily-log.update', $edlNumber], 'method' => 'PUT', 'id' => 'edit-employee-log']) }}


                            <h2 class="font-weight-bold mb-2">EDL</h2>
                            @foreach($data['edl'] as $edl)

                                <div class="row">

{{--                                    <div class="col-md-4">--}}
{{--                                        <label for="edl_number">EDL Number:</label>--}}
{{--                                        <input type="text" id="edl_number" name="edl_number" readonly value="{{ $edl['edl_number'] }}" class="form-control box-size mb-2">--}}
{{--                                    </div>--}}
                                    <div class="col-md-4">
                                        <label for="employee" >Employee:</label>
                                        <input type="text" id="employee" name="employee" readonly value="{{ $edl['employee'] }}" class="form-control box-size mb-2">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="date">Date:</label>
                                        <input type="text" id="date" name="date" readonly value="{{ $edl['date'] }}" class="form-control box-size mb-2">
                                    </div>

                                </div>
                            @endforeach

                            <h2 class="font-weight-bold mb-2">EDL Tasks</h2>

                            @php
                                $i = 1;
                            @endphp

                            @foreach($data['edlTasks'] as $task)

                                <div class="row mb-2">

                                    <div class="col-12 mb-1">{{ 'Task ' . $i }}</div>

                                    <div class="col-8 col-lg-8">
                                        <label for="subcategory{{$task['et_number']}}" >Category:</label>
                                        @if(empty($taskCategories[0]))
                                            <h5> No Task Categories Allocated to You </h5>
                                        @endif
                                        <select id="subcategory{{$task['et_number']}}" @if($i === 1) required @endif class="form-control box-size" name="subcategory{{$task['et_number']}}">
                                            <option value="">-- Select Category --</option>
                                            @foreach ($taskCategories as $cat)
                                                <option value="{{ $cat['value'] }}" @if ($cat['value'] === $task['subcategory']) selected @endif>
                                                    {{ array_search($cat ,$taskCategories) + 1 . '. ' . $cat['label'] . '  |  ' . $cat['frequency'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <code>{{$task['category']}}</code>
                                    </div>

                                    <div class="col-4 col-lg-1">
                                        <label for="hours{{$task['et_number']}}">Hours:</label>
                                        <input type="number" max="9" id="hours{{$task['et_number']}}" name="hours{{$task['et_number']}}" @if($i === 1) required @endif value="{{ $task['hours'] }}" step="0.01" class="form-control box-size">
                                    </div>

                                    <div  class="col-12 col-lg-9">
                                        <label for="description{{$task['et_number']}}">Description:</label>
                                        <textarea id="description{{$task['et_number']}}" name="description{{$task['et_number']}}" @if($i === 1) required @endif class="form-control box-size" rows="3">{{ $task['description'] }}</textarea>
                                    </div>

                                </div>

                                <hr>

                                @php
                                    $i++;
                                @endphp


                            @endforeach


                            <div>
                                @for($i = 0; $i < 20; $i++)
                                    <div class="row" id="task{{$i}}">

                                        <div class="col-12 mb-1">{{ 'New Task ' . ($i + 1) }}</div>


                                        <div class="col-8 col-lg-8">
                                            <label for="subcategory{{$i}}">Category:</label>
                                            <select class="form-control box-size" id="subcategory{{$i}}" name="subcategory{{$i}}">
                                                <option value="">-- Select Category --</option>
                                                @if(empty($taskCategories[0]))
                                                    <option value="">No Categories Created for Your Department</option>
                                                @endif
                                                @foreach ($taskCategories as $cat)
                                                    <option value="{{ $cat['value'] }}">
                                                        {{ array_search($cat ,$taskCategories) + 1 . '. ' . $cat['label'] . '  |  ' . $cat['frequency'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-5 col-lg-1 mt-1 mt-lg-0">
                                            <label for="hours{{$i}}" >Hours:</label>
                                            <input type="number" max="9" id="hours{{$i}}" name="hours{{$i}}" step="0.01" class="form-control box-size">
                                        </div>

                                        <div class="col-12 col-lg-9 mt-1 mt-lg-1">
                                            <label for="description{{$i}}">Description:</label>
                                            <textarea id="description{{$i}}" name="description{{$i}}" class="form-control box-size mb-2" rows="4"></textarea>
                                        </div>

                                        <div id="removeButton{{$i}}" class="float-right mt-4 ml-3" >
                                            <button type="button" class="btn btn-danger"> Remove </button>
                                        </div>

                                        <hr class="col-10 mt-2 ml-2">

                                    </div>
                                @endfor

                                <button id="toggleButton" type="button" class="btn btn-secondary ml-2 mb-2">Add a Task</button>
                            </div>



                            <a href="{{ route('biller.employee-daily-log.index') }}" class="btn btn-secondary mr-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update EDL</button>

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
{{ Html::script('focus/js/select2.min.js') }}
<script>

    tinymce.init({
        selector: 'textarea',
        menubar: 'file edit view format table tools',
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link table | align lineheight | tinycomments | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
        height: 230,
    });


    // initialize datepicker
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    $('#purchase_date').datepicker('setDate', new Date());
    $('#warranty_expiry_date').datepicker('setDate', new Date());

    $(document).ready(function() {

        // Initially hide the textarea
        for(let i = 0; i < 20; i++ ) {
            $('#task' + i).hide();
        }

        for(let i = 0; i < 20; i++ ) {
            $('#removeButton' + i).click(function () {
                $('#task' + i).hide();

                $('#category' + i).val('');
                $('#hours' + i).val('');
                $('#description' + i).val('');
            });
        }

        // Attach a click event handler to the toggle button
        let taskNumber = 0;
        $('#toggleButton').click(function() {
            // Toggle the visibility of the textarea
            $('#task' + taskNumber).show();
            taskNumber++;
        });

    });


</script>
@endsection