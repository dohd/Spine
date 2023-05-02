<div class='form-group'>
    {{ Form::label( 'name', 'Employee Branch Name',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('name', null, ['class' => 'form-control round', 'placeholder' => 'Employee Branch Name']) }}
    </div>
</div>
<div class='form-group'>
    {{ Form::label( 'note','Note',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('note', null, ['class' => 'form-control round', 'placeholder' =>'Note']) }}
    </div>
</div>

@section("after-scripts")
    <script type="text/javascript">
        //Put your javascript needs in here.
        //Don't forget to put `@`parent exactly after `@`section("after-scripts"),
        //if your create or edit blade contains javascript of its own
        $(document).ready(function () {
            //Everything in here would execute after the DOM is ready to manipulated.
        });
    </script>
@endsection
