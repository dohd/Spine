@extends ('core.layouts.app')

@section ('title', trans('labels.backend.notes.management') . ' | ' . trans('labels.backend.notes.edit'))

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">{{ trans('labels.backend.notes.edit') }}</h4>
                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">
                        <div class="media-body media-right text-right">
                            @include('focus.notes.partials.notes-header-buttons')
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
                                    {{ Form::model($notes, ['route' => ['biller.notes.update', $notes], 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'PATCH', 'id' => 'edit-note']) }}
                                    <div class="form-group">
                                        @include("focus.notes.form")
                                        <div class="edit-form-btn">
                                            @if (strpos(url()->previous(), 'projects') !== false)
                                                <a href="{{ url()->previous() }}" class="btn btn-danger">{{ trans('buttons.general.cancel') }}</a>
                                            @else
                                                {{ link_to_route('biller.notes.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                            @endif 
                                            {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md']) }}
                                            <div class="clearfix"></div>
                                        </div><!--edit-form-btn-->
                                    </div><!--form-group-->
                                    {{ Form::close() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
