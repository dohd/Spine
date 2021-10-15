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
            <div class="col-sm-6"><label for="client_id" class="caption">Customer*</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-file-text-o"aria-hidden="true"></span></div>
                    <select id="person" name="client_id" class="form-control required select-box"  data-placeholder="{{trans('customers.customer')}}" ></select>
                </div>
            </div>
            <div class="col-sm-6"><label for="ref_type" class="caption">Branch</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                    <select id="branch_id" name="branch_id" class="form-control  select-box"  data-placeholder="Branch" >
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
                    <div class="col-sm-12"><h3 class="title">Lead Info</h3></div>
                </div>
                <div class="form-group row">
                    <div class="col-sm-6"><label for="reference" class="caption">Lead ID*</label>
                        <div class="input-group">
                            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                            {{ Form::number('reference', $reference, ['class' => 'form-control round', 'placeholder' => 'Lead ID', 'id' => 'reference']) }}
                        </div>
                    </div>

                        <div class="col-sm-6"><label for="date_of_request" class="caption">Date Of Request*</label>
                        <div class="input-group">
                            <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                            {{ Form::text('date_of_request', null, ['class' => 'form-control round required', 'placeholder' => trans('purchaseorders.invoicedate'),'data-toggle'=>'datepicker','autocomplete'=>'false', 'id' => 'date_of_request']) }}
                        </div>
                    </div>
                </div>

                <div class="form-group row">            
                        <div class="col-sm-12"><label for="title" class="caption"> Subject / Title*</label>
                        <div class="input-group">
                            <div class="input-group-addon"><span class="icon-bookmark-o"
                                aria-hidden="true"></span>
                            </div>
                            {{ Form::text('title', null, ['class' => 'form-control round required', 'placeholder' => 'Title']) }}
                        </div>
                    </div>
                </div>                

                <div class="form-group row">
                    <div class="col-sm-6"><label for="source" class="caption">Source*</label>
                        <div class="input-group">
                            <div class="input-group-addon"><span class="icon-file-text-o"aria-hidden="true"></span></div>
                            <select id="ref_type" name="source" class="form-control round required  ">
                                <option value="">-- Select Source --</option>
                                <option value="Emergency Call">Emergency Call</option>
                                <option value="RFQ" >RFQ</option>
                                <option value="Site Survey" >Site Survey</option>
                                <option value="Tender" >Tender</option>               
                            </select>
                        </div>
                    </div>

                    <div class="col-sm-6"><label for="employee_id" class="caption">Assign To*</label>
                        <div class="input-group">
                            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                            {{ Form::text('assign_to', null, ['class' => 'form-control round required', 'placeholder' => 'Assign To ']) }}
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
                                {{ Form::number('client_ref', $client_ref, ['class' => 'form-control round', 'placeholder' => 'Client Reference No.', 'id' => 'client_ref']) }}
                            </div>
                        </div>
                    </div>

                    <div class="form-group row status-group">                    
                        <div class="col-sm-12"><label for="refer_no" class="caption">Status</label>
                            <div class="input-group">
                                <div class="form-check">
                                    <input type="hidden" name="status" value="{{ $lead->status }}" id="status">
                                    <input class="form-check-input" type="checkbox" id="statusCheckbox">
                                    <label class="form-check-label" for="status">Open</label>
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
{{ Html::script('core/app-assets/vendors/js/extensions/sweetalert.min.js') }}

<script type="text/javascript">
    /** 
     * Create Lead Form Inputs Script
    */

    // on selecting a payer type
    $("input[name=client_status]").on('change', function () {
        var payerType = $('input[name=client_status]:checked').val();

        if (payerType !== 'customer') {
            $('#person').attr('disabled',true);
            $('#branch_id').attr('disabled',true);
            
            $('#person').val('');
            $('#branch_id').val('');
            $('#payer-name').attr('readonly',false);
            $('#client_email').attr('readonly',false);
            $('#client_contact').attr('readonly',false);

            $('#payer-name').val('');
            $('#client_email').val('');
            $('#client_contact').val('');
        }else{
            $('#person').attr('disabled',false);
            $('#branch_id').attr('disabled',false);
            $('#person').val('');
            $('#branch_id').val('');

            $('#payer-name').attr('readonly',true);
            $('#client_email').attr('readonly',true);
            $('#client_contact').attr('readonly',true);
            $('#client_email').val('');
            $('#client_contact').val('');
        }
    });

    // initialize datepicker with current date parsed by php date function
    const now = "{{ date('Y-m-d') }}";
    $('[data-toggle="datepicker"]')
        .datepicker({ format: "{{config('core.user_date_format')}}"})
        .datepicker('setDate', new Date(now));

    // set ajax headers
    const token = $('meta[name="csrf-token"]').attr('content');
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': token }});

    // fetch customers
    $("#person").select2({
        tags: [],
        ajax: {
            url: "{{route('biller.customers.select')}}",
            dataType: 'json',
            type: 'POST',
            quietMillis: 50,
            data: function (person) {
                return {person};
            },
            processResults: function (data) {
                return {
                    results: $.map(data, function (item) {
                        return {
                            text: item.name+' - '+item.company,
                            id: item.id
                        }
                    })
                };
            },
        }
    });

    // on selecting a customer
    $("#person").on('change', function () {
        $("#branch_id").val('').trigger('change');
        const id = $('#person :selected').val();

        // fetch branches with params id from selected customer
        $("#branch_id").select2({
            ajax: {
                url: "{{route('biller.branches.branch_load')}}?id=" + id,
                dataType: 'json',
                type: 'POST',
                quietMillis: 50,
                params: {'cat_id': id},
                data: function (product) {
                    return {product};
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {text: item.name, id: item.id}
                        })
                    };
                },
            }
        });
    });

    // remove visibility for status checkbox
    $('.status-group').css('display', 'none');

    /** 
     * Edit Lead Form Inputs Script
     * 
     * @var lead object
     * @var branch object
     * @var customer object
    */

    const lead = @json($lead);
    const branch = @json($branch);
    const customer = @json($customer);

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
    
            $('#payer-name').prop('readonly',true);
            $('#client_email').prop('readonly',true);
            $('#client_contact').prop('readonly',true);

            // set default select option for customer and branch
            $('#person').select2({data: [{id: customer['id'], text: customer['name']}]});
            $('#branch_id').select2({data: [{id: branch['id'], text: branch['name']}]});
        } 
        $('input[type=radio]').prop('disabled', true);
        $('#person').prop('disabled', true);
        $('#branch_id').prop('disabled', true);
        $('#reference').val(lead['reference']);
        $('#ref_type').val(lead['source']);

        // parse date using php date function
        const date = "{{ date('Y-m-d', strtotime($lead['date_of_request'])) }}";
        // set datepicker with parsed date
        $('[data-toggle="datepicker"]').datepicker('setDate', new Date(date));

        // set status checkbox to be visible
        $('.status-group').css('display', 'block');
        // if lead status is 1 then, display text Closed
        if (lead['status']) {
            $('#statusCheckbox').prop('checked', true);
            $('label[for=status]').text('Closed');
        }
        // change status value when checkbox is clicked
        $('input[type=checkbox]').change(function() {
            if ($(this).is(':checked')) { 
                $('label[for=status]').text('Closed');
                $('#status').val(1);
            } else {
                $('label[for=status]').text('Open');
                $('#status').val(0);
            }
        });
    }
</script>
@endsection
