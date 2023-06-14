@extends ('core.layouts.app')

@section ('title', trans('labels.backend.hrms.management'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-2">
        <div class="content-header-left col-md-6 col-12 ">
            <h4 class="content-header-title mb-0">{{ trans('labels.backend.access.roles.management') }}</h4>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.hrms.partials.role-header-buttons')
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
                            {{ Form::open(['route' => 'biller.role.store', 'method' => 'post', 'id' => 'create-role']) }}
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            {{ Form::label('name', trans('validation.attributes.backend.access.roles.name'), ['class' => 'col-lg-2 control-label required']) }}
                                            <div class="col-md-12">
                                                {{ Form::text('name', null, ['class' => 'form-control box-size', 'placeholder' => trans('validation.attributes.backend.access.roles.name'), 'required' => 'required']) }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            {{ Form::label('status', trans('validation.attributes.backend.access.roles.active'), ['class' => 'col-lg-2 control-label']) }}
                                            <div class="col-md-12">
                                                <div class="control-group">
                                                    <label class="control control--checkbox">
                                                        {{ Form::checkbox('status', 1, true) }}
                                                        <div class="control__indicator"></div>
                                                    </label>
                                                </div>
                                            </div><!--col-lg-3-->
                                        </div><!--form control-->
                                    </div>
                                </div>

                                <div class="form-group">                                    
                                    <div class="col-md-8">
                                        {{ Form::label('associated_permissions', trans('validation.attributes.backend.access.roles.associated_permissions'), ['class' => 'control-label']) }}
                                        {{-- {{ Form::select('associated_permissions', array('none' => trans('meta.select'), 'custom' => trans('hrms.permissions')), 'none', ['class' => 'form-control select2 box-size']) }} --}}
                                        <div class="row mt-2 pl-3">
                                            @if (@$permissions->count())
                                                @foreach ($permissions as $perm)
                                                    <div class="col-md-6">
                                                        <label class=" control-checkbox">
                                                            <input class="icheckbox_square icheckbox_flat-blue"
                                                                type="checkbox"
                                                                name="permissions[{{ $perm->id }}]"
                                                                value="{{ $perm->id }}"
                                                                id="perm_{{ $perm->id }}" {{ is_array(old('permissions')) && in_array($perm->id, old('permissions')) ? 'checked' : '' }} 
                                                            />
                                                            <label for="perm_{{ $perm->id }}">{{ $perm->display_name }}</label>
                                                            <div class="control__indicator"></div>
                                                        </label>
                                                        <br/>
                                                    </div>
                                                @endforeach
                                            @else
                                                <p>There are no available permissions.</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="edit-form-btn ml-3">
                                    {{ link_to_route('biller.role.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                    {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-md']) }}
                                    <div class="clearfix"></div>
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
