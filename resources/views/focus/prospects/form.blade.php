
    <div class="col-sm-12  cmp-pnl">
        <div id="customerpanel" class="inner-cmp-pnl">
            <div class="form-group row">
                <div class="fcol-sm-12">
                    <h3 class="title pl-1">Customer Info </h3>
                </div>
            </div>
            
            <div class="form-group row">
                <div class="col-sm-12"><label for="prospect_company" class="caption">Prospect Company</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                        {{ Form::text('company', null, ['class' => 'form-control round', 'placeholder' => 'Company', 'id'=>'prospect_company']) }}
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-6"><label for="prospect_name" class="caption">Prospect Name</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                        {{ Form::text('name', null, ['class' => 'form-control round', 'placeholder' => 'Name', 'id'=>'prospect_name']) }}
                    </div>
                </div>
                <div class="col-sm-6"><label for="prospect_email" class="caption">Prospect Email</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                        {{ Form::text('email', null, ['class' => 'form-control round', 'placeholder' => 'Email','id'=>'prospect_email']) }}
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-6"><label for="prospect_contact" class="caption">Prospect Contact</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                        {{ Form::text('phone', null, ['class' => 'form-control round', 'placeholder' => 'Contact','id'=>'prospect_contact']) }}
                    </div>
                </div>
                <div class="col-sm-6"><label for="reminder_date" class="caption">Reminder Date</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                        {{ Form::text('reminder_date', null, ['class' => 'form-control round datepicker', 'placeholder' => 'Date', 'id' => 'reminder_date']) }}
                    </div>
                </div>
            </div>         
        </div>
    </div>

    


@section("after-scripts")
@include('focus.prospects.form_js')
@endsection