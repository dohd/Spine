@extends ('core.layouts.app')

@section('title', trans('business.company_settings'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">{{ trans('business.company_settings') }}</h4>
        </div>
    </div>

    <div class="content-body">
        {{ Form::open(['route' => 'biller.business.update_settings', 'method' => 'POST', 'files' => true, 'id' => 'manage-company']) }}
            <div class="row">
                <div class="col-6">
                    <div class="card rounded">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="form-group">
                                    <div class='form-group'>
                                        {{ Form::label('cname', trans('hrms.company'), ['class' => 'col control-label']) }}
                                        <div class='col'>
                                            {{ Form::text('cname', @$company['cname'], ['class' => 'form-control box-size', 'placeholder' => trans('hrms.company')]) }}
                                        </div>
                                    </div>
                                    <div class='form-group'>
                                        {{ Form::label('address', trans('hrms.address_1'), ['class' => 'col control-label']) }}
                                        <div class='col'>
                                            {{ Form::text('address', @$company['address'], ['class' => 'form-control box-size', 'placeholder' => trans('hrms.address_1')]) }}
                                        </div>
                                    </div>
                                    <div class='form-group'>
                                        {{ Form::label('city', trans('hrms.city'), ['class' => 'col control-label']) }}
                                        <div class='col'>
                                            {{ Form::text('city', @$company['city'], ['class' => 'form-control box-size', 'placeholder' => trans('hrms.city')]) }}
                                        </div>
                                    </div>
                                    <div class='form-group'>
                                        {{ Form::label('region', trans('hrms.state'), ['class' => 'col control-label']) }}
                                        <div class='col'>
                                            {{ Form::text('region', @$company['region'], ['class' => 'form-control box-size', 'placeholder' => trans('hrms.state')]) }}
                                        </div>
                                    </div>
                                    <div class='form-group'>
                                        {{ Form::label('country', trans('hrms.country'), ['class' => 'col control-label']) }}
                                        <div class='col'>
                                            {{ Form::text('country', @$company['country'], ['class' => 'form-control box-size', 'placeholder' => trans('hrms.country')]) }}
                                        </div>
                                    </div>
                                    <div class='form-group'>
                                        {{ Form::label('postbox', trans('hrms.postal'), ['class' => 'col control-label']) }}
                                        <div class='col'>
                                            {{ Form::text('postbox', @$company['postbox'], ['class' => 'form-control box-size', 'placeholder' => trans('hrms.postal')]) }}
                                        </div>
                                    </div>
                                    <div class='form-group col'>
                                        <div class="row">
                                            <div class='col'>
                                                {{ Form::label('taxid', trans('hrms.tax_id'), ['class' => 'col control-label']) }}
                                                {{ Form::text('taxid', @$company['taxid'], ['class' => 'form-control box-size', 'placeholder' => trans('hrms.tax_id')]) }}
                                            </div>
                                            <div class='col'>
                                                {{ Form::label('etr_code', 'ETR Code', ['class' => 'col control-label']) }}
                                                {{ Form::text('etr_code', @$company['etr_code'], ['class' => 'form-control box-size', 'placeholder' => 'ETR Code']) }}
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class='form-group'>
                                        {{ Form::label('email', trans('general.email'), ['class' => 'col control-label']) }}
                                        <div class='col'>
                                            {{ Form::text('email', @$company['email'], ['class' => 'form-control box-size', 'placeholder' => trans('general.email')]) }}
                                        </div>
                                    </div>
                                    <div class='form-group'>
                                        {{ Form::label('phone', trans('general.phone'), ['class' => 'col control-label']) }}
                                        <div class='col'>
                                            {{ Form::text('phone', @$company['phone'], ['class' => 'form-control box-size', 'placeholder' => trans('general.phone')]) }}
                                        </div>
                                    </div>
                                    <div class='form-group row pl-1'>
                                        <div class="col-3">
                                            <label for="clock_in">Clock-in Time</label>
                                            {{ Form::input('time', 'clock_in', @$company->clock_in, ['class' => 'form-control', 'placeholder' => 'HH:MM']) }}
                                        </div>
                                        <div class="col-3">
                                            <label for="clock_out">Clock-out Time</label>
                                            {{ Form::input('time', 'clock_out', @$company->clock_out, ['class' => 'form-control', 'placeholder' => 'HH:MM']) }}
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <div class="col-12">{!! $fields_data !!}</div>
                                    </div>
                                    <div class="edit-form-btn">
                                        {{ link_to_route('biller.dashboard', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                        {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- File Upload Section --}}
                <div class="col-6">
                    <div class="card rounded">
                        <div class="card-content">
                            <div class="card-body">
                                {{ Form::label('icon', trans('business.favicon'), ['class' => 'control-label']) }}
                                <p class="mb-2"><br><img class="img-fluid"
                                        src="{{ Storage::disk('public')->url('app/public/img/company/ico/' . @$company['icon']) }}"
                                        alt="Business favicon"></p>
                                {!! Form::file('icon', ['class' => 'input mb-1']) !!}
                                <small>{{ trans('hrms.blank_field') }}<br>only .ico format accepted
                                </small>
                                <hr>
                                {{ Form::label('theme_logo', trans('business.theme_logo'), ['class' => 'control-label']) }}
                                <p class="mb-2"><br><img class="img-fluid avatar-100"
                                        src="{{ Storage::disk('public')->url('app/public/img/company/theme/' . @$company['theme_logo']) }}"
                                        alt="Business header logo"></p>
                                {!! Form::file('theme_logo', ['class' => 'input mb-1']) !!}
                                <small>{{ trans('hrms.blank_field') }}<br>only jpg|png format accepted.<br>Recommended
                                    dimensions are
                                    80x80. Use small size file - it will load quickly.
                                </small>
                                <hr>
                                {{ Form::label('logo', trans('business.invoice_logo'), ['class' => 'control-label']) }}
                                <p class="mb-2"><br><img class="img-fluid avatar-lg"
                                        src="{{ Storage::disk('public')->url('app/public/img/company/' . @$company['logo']) }}"
                                        alt="Business Logo"></p>
                                {!! Form::file('logo', ['class' => 'input mb-2']) !!}
                                <small>{{ trans('hrms.blank_field') }}<br>only jpg|png format accepted. <br>Recommended
                                    dimensions are
                                    500x280. Use small size file - it will load quickly.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        {{ Form::close() }}
    </div>
</div>
@endsection