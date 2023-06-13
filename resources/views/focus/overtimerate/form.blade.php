<div class='form-group'>
    {{ Form::label( 'name','OverTime Rate Name',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('name', null, ['class' => 'form-control round', 'placeholder' =>'OverTime Rate Name']) }}
    </div>
</div>
<div class='form-group'>
    {{ Form::label( 'rate_by','Percentage/Value',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        <select class="form-control round" name="rate_option" id="departmentbox" data-placeholder="Select Rate/Value">
            <option value="percentage">Percentage</option>
            <option value="value">Value</option>
        </select>
    </div>
</div>
<div class='form-group'>
    {{ Form::label( 'rate', 'Rate of Basic Pay',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('rate', null, ['class' => 'form-control round', 'placeholder' => 'Rate of Basic Pay']) }}
        {{-- <select class="form-control round" id="departmentbox" data-placeholder="Search Department"></select>
        <input type="hidden" name="department_id" value="{{ @$jobtitles->department ?: 1 }}" id="departmentid">
         <input type="hidden" name="department" value="{{ @$jobtitles->department?: 1 }}" id="department"> --}}
    </div>
</div>
<div class='form-group'>
    {{ Form::label( 'note', 'Note',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('note', null, ['class' => 'form-control round', 'placeholder' => 'Note']) }}
    </div>
</div>

@section("after-scripts")
{{ Html::script('focus/js/select2.min.js') }}
    <script type="text/javascript">
        $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});


    </script>
@endsection
