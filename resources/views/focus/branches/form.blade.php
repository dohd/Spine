
<div class="form-group">
    {{$item->company}}
            {{ Form::label( 'rel_id', 'Customer',['class' => 'col-lg-2 control-label']) }}
            <div class='col'>
                <select class="form-control col-lg-10" name="rel_id" id="rel_id">
                    @foreach($customers as $item)
                     
                            <option value="{{$item->id}}" {{ $item->id === @$branches->rel_id ? " selected" : "" }}>{{$item->company}}</option>
           
                    @endforeach

                </select>
            </div>
        </div>


<div class='form-group'>
    {{ Form::label( 'name', 'Branch Name',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('name', null, ['class' => 'form-control box-size', 'placeholder' => 'Branch Name*','required'=>'required']) }}
    </div>
</div>
<div class='form-group'>
    {{ Form::label( 'location', 'Physical Location',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('location', null, ['class' => 'form-control box-size', 'placeholder' => 'Physical Address*','required'=>'required']) }}
    </div>
</div>

<div class='form-group'>
    {{ Form::label( 'contact_name', 'Contact Person',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('contact_name', null, ['class' => 'form-control box-size', 'placeholder' => 'Contact Person']) }}
    </div>
</div>
<div class='form-group'>
    {{ Form::label( 'contact_phone', 'Contact Person Contact',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('contact_phone', null, ['class' => 'form-control box-size', 'placeholder' => 'Contact Person']) }}
    </div>
</div>
