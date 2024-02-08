<div class="card-content">
    <div class="card-body">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="base-tab1" data-toggle="tab" aria-controls="tab1" href="#tab1" role="tab" aria-selected="true">{{trans('customers.billing_address')}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="base-tab2" data-toggle="tab" aria-controls="tab2" href="#tab2" role="tab" aria-selected="false">{{trans('customers.shipping_address')}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="base-tab3" data-toggle="tab" aria-controls="tab3" href="#tab3" role="tab" aria-selected="false">Opening Balance</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="base-tab4" data-toggle="tab" aria-controls="tab4" href="#tab4" role="tab" aria-selected="false">{{trans('general.other')}}</a>
            </li>
        </ul>
        <div class="tab-content px-1">
            <!-- billing address -->
            <div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="base-tab1">
                <div class="row">
                    <div class="col-sm-6">
                        <div class='form-group'>
                            {{ Form::label('company', 'Company Name',['class' => 'col-lg-6 control-label']) }}
                            <div class='col-lg-12'>
                                {{ Form::text('company', null, ['class' => 'form-control box-size', 'placeholder' => 'Company Name' . '*', 'required']) }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-sm-6">
                        <div class='form-group'>
                            {{ Form::label( 'phone', trans('customers.phone'),['class' => 'col-lg-6 control-label']) }}
                            <div class='col-md-12'>
                                {{ Form::text('phone', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.phone')]) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class='form-group'>
                            {{ Form::label( 'email', trans('customers.email'),['class' => 'col-lg-6 control-label']) }}
                            <div class='col-lg-12'>
                                {{ Form::email('email', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.email')]) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class='form-group'>
                            {{ Form::label('name', 'Contact Name', ['class' => 'col-lg-2 control-label']) }}
                            <div class='col-md-12'>
                                {{ Form::text('name', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.name')]) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class='row'>
                    <div class="col-sm-6">
                        <div class='form-group'>
                            {{ Form::label( 'address', trans('customers.address') . ' (P.O Box, City)', ['class' => 'col-lg-6 control-label']) }}
                            <div class='col-sm-12'>
                                {{ Form::text('address', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.address') . ' (P.O Box, City)']) }}
                            </div>
                        </div>  
                    </div>                                      
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class='form-group'>
                            {{ Form::label( 'city', trans('customers.city'),['class' => 'col-lg-6 control-label']) }}
                            <div class='col-md-12'>
                                {{ Form::text('city', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.city')]) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class='form-group'>
                            {{ Form::label( 'region', trans('customers.region'),['class' => 'col-lg-6 control-label']) }}
                            <div class='col-md-12'>
                                {{ Form::text('region', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.region')]) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class='form-group'>
                            {{ Form::label( 'country', trans('customers.country'),['class' => 'col-lg-6 control-label']) }}
                            <div class='col-md-12'>
                                {{ Form::text('country', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.country')]) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class='form-group'>
                            {{ Form::label( 'postbox', 'Company Location',['class' => 'col-lg-6 control-label']) }}
                            <div class='col-md-12'>
                                {{ Form::text('postbox', null, ['class' => 'form-control box-size', 'placeholder' => 'Location']) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class='form-group'>
                            {{ Form::label('taxid', trans('customers.taxid'),['class' => 'col-lg-6 control-label']) }}
                            <div class='col-lg-12'>
                                {{ Form::text('taxid', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.taxid')]) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class='form-group'>
                            <div class='col-lg-12'>
                                <label for="tax_exempt">Is Tax Exempted</label>
                                <select name="is_tax_exempt" id="is_tax_exempt" class="custom-select">
                                    @foreach (['No', 'Yes'] as $key => $val)
                                        <option value="{{ $key }}" {{ @$customer->is_tax_exempt == $key? 'selected' : '' }}>{{ $val }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- shipping address -->
            <div class="tab-pane" id="tab2" role="tabpanel" aria-labelledby="base-tab2">
                <div class="row">
                    <div class="col-sm-6">
                        <div class='form-group'>
                            {{ Form::label( 'name_s', trans('customers.name_s'),['class' => 'col-lg-12 control-label']) }}
                            <div class='col-lg-12'>
                                {{ Form::text('name_s', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.name_s')]) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class='form-group'>
                            {{ Form::label( 'phone_s', trans('customers.phone_s'),['class' => 'col-lg-12 control-label']) }}
                            <div class='col-lg-12'>
                                {{ Form::text('phone_s', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.phone_s')]) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class='form-group'>
                            {{ Form::label( 'email_s', trans('customers.email_s'),['class' => 'col-lg-12 control-label']) }}
                            <div class='col-lg-12'>
                                {{ Form::text('email_s', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.email_s')]) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class='form-group'>
                            {{ Form::label( 'address_s', trans('customers.address_s'),['class' => 'col-lg-12 control-label']) }}
                            <div class='col-lg-12'>
                                {{ Form::text('address_s', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.address_s')]) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class='form-group'>
                            {{ Form::label( 'city_s', trans('customers.city_s'),['class' => 'col-lg-12 control-label']) }}
                            <div class='col-lg-12'>
                                {{ Form::text('city_s', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.city_s')]) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class='form-group'>
                            {{ Form::label( 'region_s', trans('customers.region_s'),['class' => 'col-lg-12 control-label']) }}
                            <div class='col-lg-12'>
                                {{ Form::text('region_s', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.region_s')]) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class='form-group'>
                            {{ Form::label( 'country_s', trans('customers.country_s'),['class' => 'col-lg-12 control-label']) }}
                            <div class='col-lg-12'>
                                {{ Form::text('country_s', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.country_s')]) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class='form-group'>
                            {{ Form::label( 'postbox_s', trans('customers.postbox_s'),['class' => 'col-lg-12 control-label']) }}
                            <div class='col-lg-12'>
                                {{ Form::text('postbox_s', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.postbox_s')]) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- opening balance -->
            <div class="tab-pane" id="tab3" role="tabpanel" aria-labelledby="base-tab3">
                <div class='form-group'>
                    {{ Form::label('opening_balance', 'Opening Balance', ['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('open_balance', '0.00', ['class' => 'form-control', 'id' => 'open_balance']) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label('as_at_date', 'As At Date',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('open_balance_date', null, ['class' => 'form-control datepicker', 'id' => 'open_balance_date']) }}
                    </div>
                </div>      
                <div class='form-group'>
                    {{ Form::label('note', 'Note',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('open_balance_note', null, ['class' => 'form-control', 'id' => 'open_balance_note']) }}
                    </div>
                </div>                  
            </div>

            <!-- other details -->
            <div class="tab-pane" id="tab4" role="tabpanel" aria-labelledby="base-tab4">
                {!! @$fields !!}
                <div class='form-group'>
                    <label for="contact_person_info" class="col-2">Contact Person Info</label>
                    <div class='col-10'>
                        {{ Form::textarea('contact_person_info', null, ['class' => 'form-control', 'placeholder' => 'Contact Person Info', 'rows' => '5']) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'docid', trans('customers.docid'),['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('docid', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.docid')]) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'custom1', trans('customers.custom1'),['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('custom1', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.custom1')]) }}
                    </div>
                </div>
                <div class='form-group hide_picture'>
                    {{ Form::label('picture', trans('customers.picture'),['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-6'>
                        {!! Form::file('picture', array('class'=>'input' )) !!}
                    </div>
                </div>
                <hr>

                {{-- User Info --}}
                <h6 class="mb-2">User Info</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-12">
                                <div class='form-group'>
                                    {{ Form::label('first_name', 'First Name',['class' => 'col-12 control-label']) }}
                                    <div class='col-12'>
                                        {{ Form::text('first_name', @$customer->user->first_name, ['class' => 'form-control box-size', 'placeholder' => 'First Name']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class='form-group'>
                                    {{ Form::label('last_name', 'Last Name',['class' => 'col-12 control-label']) }}
                                    <div class='col-12'>
                                        {{ Form::text('last_name', @$customer->user->last_name, ['class' => 'form-control box-size', 'placeholder' => 'Last Name']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class='form-group'>
                                    {{ Form::label('user_email', 'Email', ['class' => 'col-12 control-label']) }}
                                    <div class='col-12'>
                                        {{ Form::text('user_email', @$customer->user->email, ['class' => 'form-control box-size', 'placeholder' => 'Email']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="row">
                            <div class="col-md-12">
                                <div class='form-group'>
                                    {{ Form::label( 'password', trans('customers.password'),['class' => 'col-12 control-label']) }}
                                    <div class='col-12'>
                                        {{ Form::password('password', ['class' => 'form-control box-size', 'id' => 'password']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class='form-group'>
                                    {{ Form::label('password_confirmation', 'Confirm Password',['class' => 'col-12 control-label']) }}
                                    <div class='col-12'>
                                        {{ Form::password('password_confirmation', ['class' => 'form-control box-size', 'id' => 'confirm_password']) }}
                                        <label for="password_match" class="text-danger d-none">Password does not match !</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="ml-2 password-condition">
                                    <h4>Password must have:</h4>
                                    <h5 class="text-danger"><i class="fa fa-check" aria-hidden="true"></i> At least 7 characters</h5>
                                    <h5 class="text-danger"><i class="fa fa-check" aria-hidden="true"></i> Contain Upper and Lowercase letters</h5>
                                    <h5 class="text-danger"><i class="fa fa-check" aria-hidden="true"></i> At least one number</h5>
                                    <h5 class="text-danger"><i class="fa fa-check" aria-hidden="true"></i> At least one symbol</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section("after-scripts")
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    const config = {
        date: {format: "{{config('core.user_date_format')}}", autoHide: true}
    };

    const Form = {
        customer: @json(@$customer),

        init() {
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());

            if (Form.customer && Form.customer.id) {
                const date = Form.customer.open_balance_date;
                const balance = parseFloat(Form.customer.open_balance);
                if (date) $('#open_balance_date').datepicker('setDate', new Date(date));
                $('#open_balance').val(accounting.formatNumber(balance));
            }
            $('#password').on('keyup', Form.passwordKeyUp);
            $('#confirm_password').on('keyup', Form.confirmPasswordKeyUp);
            $('#open_balance').change(Form.openBalanceChange);
        },

        passwordKeyUp() {
            const div = $('.password-condition');
            const value = $(this).val();
            if (value.length >= 7) div.find('h5:first').removeClass('text-danger').addClass('text-success');
            else div.find('h5:first').removeClass('text-success').addClass('text-danger');
            if (new RegExp("[a-z][A-Z]|[A-Z][a-z]").test(value)) div.find('h5:eq(1)').removeClass('text-danger').addClass('text-success');
            else div.find('h5:eq(1)').removeClass('text-success').addClass('text-danger');
            if (new RegExp("[0-9]").test(value)) div.find('h5:eq(-2)').removeClass('text-danger').addClass('text-success');
            else div.find('h5:eq(-2)').removeClass('text-success').addClass('text-danger');
            if (new RegExp("[^A-Za-z 0-9]").test(value)) div.find('h5:last').removeClass('text-danger').addClass('text-success');
            else div.find('h5:last').removeClass('text-success').addClass('text-danger');
        },

        confirmPasswordKeyUp() {
            if ($(this).val() != $('#password').val()) $(this).next().removeClass('d-none');
            else $(this).next().addClass('d-none');
        },

        openBalanceChange() {
            const balance = accounting.unformat($(this).val());
            $(this).val(accounting.formatNumber(balance));
        },
    };

    $(Form.init);
</script>
@endsection