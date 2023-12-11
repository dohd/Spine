@extends ('core.layouts.app')

@section ('title', 'Edit Task Subcategory')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h3 class="mb-0">Edit Task</h3>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card" style="border-radius: 8px;">
                    <div class="card-content">
                        <div class="card-body">
                            {{ Form::model($taskSubcategory, ['route' => ['biller.employee-task-subcategories.update', $taskSubcategory->id], 'method' => 'PATCH']) }}
                            <div class="form-group">
                                {{-- Including Form blade file --}}

                                <div class="row mb-2">

                                    <div class="col-10 col-lg-7">
                                        <label for="department">Department:</label>
                                        <select class="form-control box-size" id="department" name="department">
                                            <option value="">-- Select Department --</option>
                                            @foreach ($departments as $val)
                                                <option value="{{ $val }}" @if ($val == $taskSubcategory->department) selected @endif>
                                                    {{ $val }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-10 col-lg-7">
                                        <label for="name" class="mt-2">Subcategory Name:</label>
                                        <input type="text" id="name" name="name" required class="form-control box-size mb-2" value="{{ $taskSubcategory->name }}">
                                    </div>

                                    <div class="col-10 col-lg-7">
                                        <label for="frequency">Frequency:</label>
                                        <select class="form-control box-size" id="frequency" name="frequency">
                                            <option value="">-- Select Frequency --</option>
                                            @foreach ($frequency as $val)
                                                <option value="{{ $val }}"  @if ($val == $taskSubcategory->frequency) selected @endif>
                                                    {{ $val }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                </div>

                                <div class="edit-form-btn">
                                    {{ link_to_route('biller.employee-task-subcategories.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-secondary btn-md mr-1']) }}
                                    {{ Form::submit('Update', ['class' => 'btn btn-primary btn-md']) }}
                                    <div class="clearfix"></div>
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

<style>
    .radius-8 {
        border-radius: 8px;
    }
</style>