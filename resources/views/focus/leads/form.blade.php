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
                        <div><label for="client-type">Select Client Type</label></div>                        
                        <div class="d-inline-block custom-control custom-checkbox mr-1">
                            <input type="radio" class="custom-control-input bg-primary clientstatus" name="client_status" id="colorCheck1" value="customer" checked>
                            <label class="custom-control-label" for="colorCheck1">Existing</label>
                        </div>
                        <div class="d-inline-block custom-control custom-checkbox mr-1">
                            <input type="radio" class="custom-control-input bg-purple clientstatus" name="client_status" value="new" id="colorCheck3">
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
                <div class="col-sm-6"><label for="client_name" class="caption">Client Name</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                        {{ Form::text('client_name', null, ['class' => 'form-control round', 'placeholder' => 'Name', 'id'=>'payer-name', 'readonly']) }}
                    </div>
                </div>
                <div class="col-sm-6"><label for="client_email" class="caption">Client Email</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                        {{ Form::text('client_email', null, ['class' => 'form-control round', 'placeholder' => 'Email','id'=>'client_email', 'readonly']) }}
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-6"><label for="client_contact" class="caption">Client Contact</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                        {{ Form::text('client_contact', null, ['class' => 'form-control round', 'placeholder' => 'Contact','id'=>'client_contact', 'readonly']) }}
                    </div>
                </div>
                <div class="col-sm-6"><label for="client_address" class="caption">Client Address</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                        {{ Form::text('client_address', null, ['class' => 'form-control round', 'placeholder' => 'Contact', 'id' => 'client_address', 'readonly']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 cmp-pnl">
        <div class="inner-cmp-pnl">
            <div class="form-group row">
                <div class="col-sm-12">
                    <h3 class="title">Ticket Info</h3>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-6"><label for="reference" class="caption">Ticket No</span></label>
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
                <div class="col-sm-6"><label for="date_of_request" class="caption">Callout / Client Report Date</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                        {{ Form::text('date_of_request', null, ['class' => 'form-control round datepicker', 'id' => 'date_of_request']) }}
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
                            <option value="">-- Select Source --</option>
                            <option value="Emergency Call">Emergency Call</option>
                            <option value="RFQ">RFQ</option>
                            <option value="Site Survey">Site Survey</option>
                            <option value="Existing SLA">Existing SLA</option>
                            <option value="Tender">Tender</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6"><label for="employee_id" class="caption">Requested By (Client Rep)<span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                        {{ Form::text('assign_to', null, ['class' => 'form-control round', 'placeholder' => 'Requested By', 'required']) }}
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-6"><label for="client_ref" class="caption">Client Ref / Callout ID</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                        {{ Form::text('client_ref', null, ['class' => 'form-control round', 'placeholder' => 'Client Reference No.', 'id' => 'client_ref', 'maxlength' => 30, 'required']) }}
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-12"><label for="refer_no" class="caption">Note</label>
                    <div class="input-group">
                        <div class="w-100">
                            {{ Form::textarea('note', null, ['class' => 'form-control', 'rows' => 6]) }}
                        </div>
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
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } });

    // Initialize datepicker
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})    
    $('#date_of_request').datepicker('setDate', new Date());
    
    // On selecting client type radio
    $('.clientstatus').change(function() {
        if ($(this).val() === 'new') {
            $('#person').attr('disabled', true).val('');
            $('#branch_id').attr('disabled', true).val('');
            $('#payer-name').attr('readonly', false).val('');
            $('#client_email').attr('readonly', false).val('');
            $('#client_contact').attr('readonly', false).val('');
            $('#client_address').attr('readonly', false).val('');
        } 
        if ($(this).val() === 'customer') {
            $('#person').attr('disabled', false).val('');
            $('#branch_id').attr('disabled', false).val('');
            $('#payer-name').attr('readonly', true).val('');
            $('#client_email').attr('readonly', true).val('');;
            $('#client_contact').attr('readonly', true).val('');
            $('#client_address').attr('readonly', true).val('');
        }
    });

    /**
     * Edit Lead form script
     */
    const lead = @json(@$lead);
    const branch = @json(@$lead->branch);
    const customer = @json(@$lead->customer);
    if (lead && lead.hasOwnProperty('branch_id')) {
        // new customer
        if (lead['client_status'] == 'new') {
            $('#colorCheck1').attr('checked', false);
            $('#colorCheck3').attr('checked', true);
            $('#person').attr('disabled', true);
            $('#branch_id').attr('disabled', true).select2();
            $('#payer-name').attr('readonly', false);
            $('#client_email').attr('readonly', false);
            $('#client_contact').attr('readonly', false);
            $('#client_address').attr('readonly', false);
        } 
        else {
            $('#colorCheck1').attr('checked', true);
            $('#colorCheck3').attr('checked', false);
            $('#payer-name').attr('readonly', true);
            $('#client_email').attr('readonly', true);
            $('#client_contact').attr('readonly', true);
            $('#client_address').attr('readonly', true);

            $('#person').append(new Option(customer['name'], customer['id']));
            $('#branch_id').append(new Option(branch['name'], branch['id']));
        }

        $('#reference').val(lead['reference']);
        $('#ref_type').val(lead['source']);
        const date = @json(date_for_database(@$lead->date_of_request));
        if (date) $('#date_of_request').datepicker('setDate', new Date(date));
    }

    // fetch customers
    $("#person").select2({
        ajax: {
            url: "{{ route('biller.customers.select') }}",
            dataType: 'json',
            type: 'POST',
            quietMillis: 50,
            data: function(params) { 
                return { search: params.term }
            },
            processResults: function(data) {
                return {
                    results: data.map(v => ({ 
                        id: v.id, 
                        text: `${v.name} - ${v.company}`,
                    }))
                };
            },
        }
    });

    // on selecting a customer
    $("#branch_id").select2();
    $("#person").change(function() {
        $("#branch_id").html('').select2({
            ajax: {
                url: "{{ route('biller.branches.select') }}",
                type: 'POST',
                quietMillis: 50,
                data: ({term}) => ({search: term, customer_id: $("#person").val()}),                                
                processResults: (data) => {
                    return { results: data.map(v => ({ text: v.name, id: v.id })) };
                },
            }
        });
    });
</script>
@endsection