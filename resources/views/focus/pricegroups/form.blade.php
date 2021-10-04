<div class='form-group'>
    {{ Form::label( 'name', 'Price Group Name',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('name', null, ['class' => 'form-control box-size required', 'placeholder' => 'Price Group Name *','required'=>'required']) }}
    </div>
</div>
<div class='form-group'>
    {{ Form::label( 'description', 'Description',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('description', null, ['class' => 'form-control box-size', 'placeholder' => 'Description ']) }}
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
