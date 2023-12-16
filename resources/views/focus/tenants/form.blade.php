<div class="row">
    <div class="col-6">
        <div class="card rounded">
            <div class="card-content">
                <div class="card-body">
                    <div class="form-group">
                        <div class='form-group'>
                            {{ Form::label('cname', trans('hrms.company'), ['class' => 'col control-label']) }}
                            <div class='col'>
                                {{ Form::text('cname', @$tenant['cname'], ['class' => 'form-control box-size', 'placeholder' => trans('hrms.company'), 'required' => 'required']) }}
                            </div>
                        </div>
                        <div class='form-group'>
                            {{ Form::label('address', trans('hrms.address_1'), ['class' => 'col control-label']) }}
                            <div class='col'>
                                {{ Form::text('address', @$tenant['address'], ['class' => 'form-control box-size', 'placeholder' => trans('hrms.address_1'), 'required' => 'required']) }}
                            </div>
                        </div>
                        <div class='form-group'>
                            {{ Form::label('country', trans('hrms.country'), ['class' => 'col control-label']) }}
                            <div class='col'>
                                {{ Form::text('country', @$tenant['country'], ['class' => 'form-control box-size', 'placeholder' => trans('hrms.country')]) }}
                            </div>
                        </div>
                        <div class='form-group'>
                            {{ Form::label('postbox', trans('hrms.postal'), ['class' => 'col control-label']) }}
                            <div class='col'>
                                {{ Form::text('postbox', @$tenant['postbox'], ['class' => 'form-control box-size', 'placeholder' => trans('hrms.postal'), 'required' => 'required']) }}
                            </div>
                        </div>                        
                        <div class='form-group'>
                            {{ Form::label('email', trans('general.email'), ['class' => 'col control-label']) }}
                            <div class='col'>
                                {{ Form::text('cemail', @$tenant['email'], ['class' => 'form-control box-size', 'placeholder' => trans('general.email'), 'required' => 'required']) }}
                            </div>
                        </div>
                        <div class='form-group'>
                            {{ Form::label('phone', trans('general.phone'), ['class' => 'col control-label']) }}
                            <div class='col'>
                                {{ Form::text('phone', @$tenant['phone'], ['class' => 'form-control box-size', 'placeholder' => trans('general.phone')]) }}
                            </div>
                        </div>
                        <div class='form-group'>
                            {{ Form::label('taxid', trans('hrms.tax_id'), ['class' => 'col control-label']) }}
                            <div class='col'>
                                {{ Form::text('taxid', @$tenant['taxid'], ['class' => 'form-control box-size', 'placeholder' => trans('hrms.tax_id')]) }}
                            </div>
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
                <div class="card-body mb-5">
                    {{ Form::label('icon', trans('business.favicon'), ['class' => 'control-label']) }}
                    <p class="mb-2"><br><img class="img-fluid"
                            src="{{ Storage::disk('public')->url('app/public/img/company/ico/' . @$tenant['icon']) }}"
                            alt="Business favicon"></p>
                    {!! Form::file('icon', ['class' => 'input mb-1']) !!}
                    <small>{{ trans('hrms.blank_field') }}<br>only .ico format accepted
                    </small>
                    <hr>
                    {{ Form::label('theme_logo', trans('business.theme_logo'), ['class' => 'control-label']) }}
                    <p class="mb-2"><br><img class="img-fluid avatar-100"
                            src="{{ Storage::disk('public')->url('app/public/img/company/theme/' . @$tenant['theme_logo']) }}"
                            alt="Business header logo"></p>
                    {!! Form::file('theme_logo', ['class' => 'input mb-1']) !!}
                    <small>{{ trans('hrms.blank_field') }}<br>only jpg|png format accepted.<br>Recommended
                        dimensions are
                        80x80. Use small size file - it will load quickly.
                    </small>
                    <hr>
                    {{ Form::label('logo', trans('business.invoice_logo'), ['class' => 'control-label']) }}
                    <p class="mb-2"><br><img class="img-fluid avatar-lg"
                            src="{{ Storage::disk('public')->url('app/public/img/company/' . @$tenant['logo']) }}"
                            alt="Business Logo"></p>
                    {!! Form::file('logo', ['class' => 'input mb-2']) !!}
                    <small>{{ trans('hrms.blank_field') }}<br>only jpg|png format accepted. <br>Recommended
                        dimensions are
                        500x280. Use small size file - it will load quickly.
                    </small>
                    <div class="mb-1"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card rounded">
            <div class="card-content">
                <div class="card-header pb-0"><h5>Super User</h5></div>
                <div class="card-body">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-6">
                                <div class='form-group'>
                                    {{ Form::label('firstname', 'First Name', ['class' => 'col control-label']) }}
                                    <div class='col'>
                                        {{ Form::text('first_name', @$user->first_name, ['class' => 'form-control box-size', 'placeholder' => 'First Name', 'required' => 'required']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class='form-group'>
                                    {{ Form::label('lastname', 'Last Name', ['class' => 'col control-label']) }}
                                    <div class='col'>
                                        {{ Form::text('last_name', @$user->last_name, ['class' => 'form-control box-size', 'placeholder' => 'Last Name', 'required' => 'required']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class='form-group'>
                                    {{ Form::label('email', 'Email', ['class' => 'col control-label']) }}
                                    <div class='col'>
                                        {{ Form::text('email', @$user->email, ['class' => 'form-control box-size', 'placeholder' => 'Email', 'required' => 'required']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class='form-group'>
                                    {{ Form::label('password', 'Password', ['class' => 'col control-label']) }}
                                    <div class='col'>
                                        {{ Form::text('password', null, ['class' => 'form-control box-size', 'placeholder' => 'Password', 'required' =>  (@$user? false : 'required')]) }}
                                    </div>
                                </div> 
                            </div>
                        </div>   
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>