{{ Form::open(['route' => ['biller.import.general','equipments'], 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post','files' => true, 'id' => 'import-data']) }}
<input type="hidden" name="update" value="1">
{!! Form::file('import_file', array('class'=>'form-control input col-md-6 mb-1' )) !!}
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            {{ Form::label( 'account', 'Client',['class' => 'col control-label']) }}
            <div class='col'>
                <select class="form-control" name="customer">
                    @foreach($data['customers'] as $customer)
                        <option value="{{$customer['id']}}">{{$customer['company']}}</option>
                    @endforeach

                </select>
            </div>
        </div>
    </div>
  
</div>
{{ Form::submit(trans('import.upload_import'), ['class' => 'btn btn-primary btn-md']) }}
{{ Form::close() }}