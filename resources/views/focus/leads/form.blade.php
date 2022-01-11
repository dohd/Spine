<div class="row">
    <div class="col-sm-6 cmp-pnl">
        <div id="customerpanel" class="inner-cmp-pnl">
            <div class="form-group row">
                <div class="fcol-sm-12">
                    <h3 class="title pl-1">Customer Info </h3>
                </div>
            </div>
            <div class="form-group row">
                <div class='col-md-12'>
                    <div class='col m-1'>
                        {{ Form::label( 'method', trans('transactions.payer_type'),['class' => 'col-12 control-label']) }}
                        <div class="d-inline-block custom-control custom-checkbox mr-1">
                            <input type="radio" class="custom-control-input bg-primary" name="client_status" id="colorCheck1" value="customer" checked="">
                            <label class="custom-control-label" for="colorCheck1">Existing</label>
                        </div>
                        <div class="d-inline-block custom-control custom-checkbox mr-1">
                            <input type="radio" class="custom-control-input bg-purple" name="client_status" value="new" id="colorCheck3">
                            <label class="custom-control-label" for="colorCheck3">New Client</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-6"><label for="client_id" class="caption">Customer <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                        <select id="person" name="client_id" class="form-control required select-box" data-placeholder="{{trans('customers.customer')}}"></select>
                    </div>
                </div>
                <div class="col-sm-6"><label for="ref_type" class="caption">Branch</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                        <select id="branch_id" name="branch_id" class="form-control  select-box" data-placeholder="Branch">
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-6"><label for="client_name" class="caption"> Name</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                        {{ Form::text('client_name', null, ['class' => 'form-control round required', 'placeholder' => 'Name','id'=>'payer-name', 'readonly']) }}
                    </div>
                </div>
                <div class="col-sm-6"><label for="client_email" class="caption"> Email</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                        {{ Form::text('client_email', null, ['class' => 'form-control round required', 'placeholder' => 'Email','id'=>'client_email', 'readonly']) }}
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-6"><label for="client_contact" class="caption"> Contact</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                        {{ Form::text('client_contact', null, ['class' => 'form-control round required', 'placeholder' => 'Contact','id'=>'client_contact', 'readonly']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 cmp-pnl">
        <div class="inner-cmp-pnl">
            <div class="form-group row">
                <div class="col-sm-12">
                    <h3 class="title">Lead Info</h3>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-6"><label for="reference" class="caption">Lead No</span></label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                        @if (isset($tid))
                            {{ Form::text('reference', 'Tkt-' . sprintf('%04d', $tid+1), ['class' => 'form-control round', 'disabled']) }}
                            <input type="hidden" name="reference" value="{{ $tid+1 }}">
                        @else
                            {{ Form::text('reference', 'Tkt-' . sprintf('%04d', $lead->reference), ['class' => 'form-control round', 'disabled']) }}
                            <input type="hidden" name="reference" value="{{ $lead->reference }}">
                        @endif
                    </div>
                </div>
                <div class="col-sm-6"><label for="date_of_request" class="caption">Callout Date</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                        {{ Form::text('date_of_request', null, ['class' => 'form-control round required', 'data-toggle'=>'datepicker', 'id' => 'date_of_request']) }}
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-12"><label for="title" class="caption"> Subject / Title <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span>
                        </div>
                        {{ Form::text('title', null, ['class' => 'form-control round', 'placeholder' => 'Title', 'required']) }}
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-6"><label for="source" class="caption">Source <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                        <select id="ref_type" name="source" class="form-control round" required>
                            <option value="0">-- Select Source --</option>
                            <option value="Emergency Call">Emergency Call</option>
                            <option value="RFQ">RFQ</option>
                            <option value="Site Survey">Site Survey</option>
                            <option value="Existing SLA">Existing SLA</option>
                            <option value="Tender">Tender</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6"><label for="employee_id" class="caption">Assign To <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                        {{ Form::text('assign_to', null, ['class' => 'form-control round', 'placeholder' => 'Assign To', 'required']) }}
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-12"><label for="refer_no" class="caption">Note</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                        {{ Form::text('note', null, ['class' => 'form-control round', 'placeholder' => trans('general.note'),'autocomplete'=>'off']) }}
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-6"><label for="client_ref" class="caption">Client Ref / Callout ID</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                        {{ Form::text('client_ref', null, ['class' => 'form-control round', 'placeholder' => 'Client Reference No.', 'id' => 'client_ref', 'required']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section("after-scripts")
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script('core/app-assets/vendors/js/extensions/sweetalert.min.js') }}

<script type="text/javascript">
    // on selecting a payer type
    $("input[name=client_status]").on('change', function() {
        var payerType = $('input[name=client_status]:checked').val();

        if (payerType !== 'customer') {
            $('#person').attr('disabled', true);
            $('#branch_id').attr('disabled', true);

            $('#person').val('');
            $('#branch_id').val('');
            $('#payer-name').attr('readonly', false);
            $('#client_email').attr('readonly', false);
            $('#client_contact').attr('readonly', false);

            $('#payer-name').val('');
            $('#client_email').val('');
            $('#client_contact').val('');
        } else {
            $('#person').attr('disabled', false);
            $('#branch_id').attr('disabled', false);
            $('#person').val('');
            $('#branch_id').val('');

            $('#payer-name').attr('readonly', true);
            $('#client_email').attr('readonly', true);
            $('#client_contact').attr('readonly', true);
            $('#client_email').val('');
            $('#client_contact').val('');
        }
    });

    $('[data-toggle="datepicker"]')
        .datepicker({
            format: "{{config('core.user_date_format')}}"
        })
        .datepicker('setDate', new Date());

    // set ajax headers
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // remove visibility for status checkbox
    $('.status-group').css('display', 'none');


    /**
     * Edit Lead form script
     */
    const lead = @json(@$lead);
    const branch = @json(@$lead->branch);
    const customer = @json(@$lead->customer);
    // if branch_id is 0 then its a new customer otherwise an existing customer
    if (lead && lead.hasOwnProperty('branch_id')) {
        if (lead['branch_id'] === 0) {
            $('#colorCheck1').prop('checked', false);
            $('#colorCheck3').prop('checked', true);

            $('#person').prop('disabled', true);
            $('#branch_id').prop('disabled', true).select2();

            $('#payer-name').prop('readonly', false);
            $('#client_email').prop('readonly', false);
            $('#client_contact').prop('readonly', false);
        } else {
            $('#colorCheck1').prop('checked', true);
            $('#colorCheck3').prop('checked', false);

            $('#payer-name').prop('readonly', true);
            $('#client_email').prop('readonly', true);
            $('#client_contact').prop('readonly', true);

            // set default select option for customer and branch
            $('#person').select2({
                data: [{
                    id: customer['id'],
                    text: customer['name']
                }]
            });
            $('#branch_id').select2({
                data: [{
                    id: branch['id'],
                    text: branch['name']
                }]
            });
        }
        $('input[type=radio]').prop('disabled', true);
        $('#reference').val(lead['reference']);
        $('#ref_type').val(lead['source']);

        // parse date using php date function
        const date = "{{ date_for_database(@$lead->date_of_request) }}";
        $('[data-toggle="datepicker"]').datepicker('setDate', new Date(date));
    }

    // fetch customers
    $("#person").select2({
        tags: [],
        ajax: {
            url: "{{route('biller.customers.select')}}",
            dataType: 'json',
            type: 'POST',
            quietMillis: 50,
            data: function(person) {
                return {
                    person
                };
            },
            processResults: function(data) {
                return {
                    results: $.map(data, function(item) {
                        return {
                            id: item.id,
                            text: `${item.name} - ${item.company}`,
                        }
                    })
                };
            },
        }
    });

    // on selecting a customer
    $("#person").on('change', function() {
        $("#branch_id").val('').trigger('change');
        const id = $('#person :selected').val();

        // fetch branches with params id from selected customer
        $("#branch_id").select2({
            ajax: {
                url: "{{route('biller.branches.branch_load')}}?id=" + id,
                dataType: 'json',
                type: 'GET',
                quietMillis: 50,
                params: {
                    'cat_id': id
                },
                data: function(product) {
                    return {
                        product
                    };
                },
                processResults: function(data) {
                    return {
                        results: $.map(data, function(item) {
                            return {
                                text: item.name,
                                id: item.id
                            }
                        })
                    };
                },
            }
        });
    });
</script>
@endsection