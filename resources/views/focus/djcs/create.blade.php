@extends ('core.layouts.app')

@section ('title', ' Diagnosis Job Card | Create Diagnosis Job Card')

@section('page-header')
<h1>
    Diagnosis Job Card
    <small>Create Diagnosis Job Card</small>
</h1>
@endsection

@section('content')
<div class="">
    <div class="content-wrapper">
        <div class="content-body">
            <div class="card">
                <div class="card-content">
                    <div class="card-body">
                        {{ Form::open(['route' => 'biller.djcs.store', 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post','files' => true, 'id' => 'create-product']) }}
                        <div class="row">
                            <div class="col-sm-6 cmp-pnl">
                                <div id="customerpanel" class="inner-cmp-pnl">
                                    <div class="form-group row">
                                        <div class="fcol-sm-12">
                                            <h3 class="title ml-1"> Djc Details</h3>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-12"><label for="ref_type" class="caption">Search Lead</label>
                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span>
                                                </div>
                                                <select class="form-control  round  select-box" name="lead_id" id="lead_id" data-placeholder="{{trans('tasks.assign')}}" required="required">
                                                    <option value="">-- Select Lead --</option>
                                                    @foreach($leads as $lead)
                                                    @php
                                                    if ($lead->client_status == "customer") {
                                                    $name = $lead->customer->company.' '. $lead->branch->name;
                                                    } else {
                                                    $name = $lead->client_name;
                                                    }
                                                    @endphp
                                                    <option value="{{$lead['id']}}">{{$lead['reference']}} - {{$name}} -
                                                        {{$lead->title}}
                                                    </option>
                                                    @endforeach
                                                </select>
                                                <input type="hidden" name="client_id" id="client_id">
                                                <input type="hidden" name="branch_id" id="branch_id">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <label for="attention" class="attention">Attention*</label>
                                            {{ Form::text('attention', null, ['class' => 'form-control round required', 'placeholder' => 'Attention','autocomplete'=>'false','id'=>'attention']) }}
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-4"><label for="tid" class="caption">Report No</label>
                                            <div class="input-group">
                                                <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span>
                                                </div>
                                                {{ Form::number('tid', @$last_djc->tid+1, ['class' => 'form-control round', 'placeholder' => 'reference','required' => 'required']) }}
                                            </div>
                                        </div>
                                        <div class="col-sm-4"><label for="reference" class="caption">Reference</label>
                                            <div class="input-group">
                                                <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span>
                                                </div>
                                                {{ Form::text('reference', null, ['class' => 'form-control round ', 'placeholder' => 'reference']) }}
                                            </div>
                                        </div>
                                        <div class="col-sm-4"><label for="report_date" class="caption">Report
                                                {{trans('general.date')}}</label>
                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span>
                                                </div>
                                                {{ Form::text('report_date', null, ['class' => 'form-control round required', 'placeholder' => trans('general.date'),'data-toggle'=>'datepicker','autocomplete'=>'false']) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-4">
                                            <label for="region" class="caption"> Region</label>
                                            {{ Form::text('region', null, ['class' => 'form-control round ', 'placeholder' => 'Region','autocomplete'=>'false','id'=>'region']) }}
                                        </div>
                                        <div class="col-sm-4"><label for="prepared_by" class="caption">Prepaired By*</label>
                                            <div class="input-group">
                                                <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span>
                                                </div>
                                                {{ Form::text('prepared_by', null, ['class' => 'form-control round', 'placeholder' => 'Prepaired By']) }}
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <label for="technician" class="caption">Technician*</label>
                                            {{ Form::text('technician', null, ['class' => 'form-control round required', 'placeholder' => 'Technician','autocomplete'=>'false','id'=>'prepaired_by','required' => 'required']) }}
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-3"><label for="client_name" class="caption"> Image 1 </label>
                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span>
                                                </div>
                                                {!! Form::file('image_one', array('class'=>'input' )) !!}
                                            </div>
                                        </div>
                                        <div class="col-sm-3"><label for="client_email" class="caption"> Image 2</label>
                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span>
                                                </div>
                                                {!! Form::file('image_two', array('class'=>'input' )) !!}
                                            </div>
                                        </div>
                                        <div class="col-sm-3"><label for="client_email" class="caption"> Image 3</label>
                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span>
                                                </div>
                                                {!! Form::file('image_three', array('class'=>'input' )) !!}
                                            </div>
                                        </div>
                                        <div class="col-sm-3"><label for="client_email" class="caption"> Image 4</label>
                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span>
                                                </div>
                                                {!! Form::file('image_four', array('class'=>'input' )) !!}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-3"><label for="caption" class="caption"> Caption 1 </label>
                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span>
                                                </div>
                                                {{ Form::text('caption_one', null, ['class' => 'form-control round ', 'placeholder' => 'Caption 1','id'=>'caption_one']) }}
                                            </div>
                                        </div>
                                        <div class="col-sm-3"><label for="client_email" class="caption"> Caption 2</label>
                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span>
                                                </div>
                                                {{ Form::text('caption_two', null, ['class' => 'form-control round ', 'placeholder' => 'Caption 2','id'=>'caption_two']) }}
                                            </div>
                                        </div>
                                        <div class="col-sm-3"><label for="caption_three" class="caption"> Caption 3</label>
                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span>
                                                </div>
                                                {{ Form::text('caption_three', null, ['class' => 'form-control round ', 'placeholder' => 'Caption 3','id'=>'caption_three']) }}
                                            </div>
                                        </div>
                                        <div class="col-sm-3"><label for="client_email" class="caption"> Caption 4</label>
                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span>
                                                </div>
                                                {{ Form::text('caption_four', null, ['class' => 'form-control round ', 'placeholder' => 'Caption 4','id'=>'caption_four']) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 cmp-pnl">
                                <div class="inner-cmp-pnl">
                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <h3 class="subject">Report</h3>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <label for="subject" class="caption">Subject / Title</label>
                                            {{ Form::text('subject', null, ['class' => 'form-control round required', 'placeholder' => 'Subject Or Title','autocomplete'=>'false','id'=>'subject']) }}
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <label for="toAddInfo" class="root_cause">Findings and Root Cause</label>
                                            {{ Form::textarea('root_cause', null, ['class' => 'form-control round html_editor', 'placeholder' => 'Root Cause','rows'=>'4']) }}
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <label for="toAddInfo" class="action_taken">Action Taken</label>
                                            {{ Form::textarea('action_taken', null, ['class' => 'form-control round html_editor', 'placeholder' => 'Action Taken','rows'=>'4']) }}
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <label for="toAddInfo" class="recommendations">Recommendations</label>
                                            {{ Form::textarea('recommendations', null, ['class' => 'form-control round html_editor', 'placeholder' => 'Recommendations','rows'=>'4']) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="saman-row">
                            <table id="quotation" class="table-responsive tfr my_stripe_single" style="padding-bottom: 100px;">
                                <thead>
                                    <tr class="item_header bg-gradient-directional-blue white">
                                        <th width="20%" class="text-center">Tag/Unique Number</th>
                                        <th width="10%" class="text-center">Jobcard</th>
                                        <th width="10%" class="text-center">Type</th>
                                        <th width="10%" class="text-center">Make</th>
                                        <th width="10%" class="text-center">Capacity</th>
                                        <th width="10%" class="text-center">Location</th>
                                        <th width="10%" class="text-center">Last Service Date</th>
                                        <th width="10%" class="text-center">Next Service Date</th>
                                        <th width="10%" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type="text" class="form-control" name="tag_number[]" placeholder="Search Equipment" id='tag_number-0' required="required"></td>
                                        <td><input type="text" class="form-control req amnt" name="joc_card[]" id="joc_card-0" autocomplete="off"></td>
                                        <td><input type="text" class="form-control req prc" name="equipment_type[]" id="equipment_type-0" autocomplete="off"></td>
                                        <td><input type="text" class="form-control r" name="make[]" id="make-0" autocomplete="off"></td>
                                        <td><input type="text" class="form-control req" name="capacity[]" id="capacity-0" autocomplete="off"></td>
                                        <td><input type="text" class="form-control req" name="location[]" id="location-0" autocomplete="off"></td>
                                        <td><input type="text" class="form-control req" name="last_service_date[]" id="last_service_date-0" autocomplete="off" data-toggle="datepicker"></td>
                                        <td><input type="text" class="form-control req" name="next_service_date[]" id="next_service_date-0" autocomplete="off" data-toggle="datepicker"></td>
                                        <td class="text-center">
                                            <div class="dropdown"><button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action</button>
                                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton"><a class="dropdown-item removeProd" href="javascript:void(0);" data-rowid="0">Remove</a><a class="dropdown-item up" href="javascript:void(0);">Up</a><a class="dropdown-item down" href="javascript:void(0);">Down</a></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="last-item-row sub_c" style="display: none"></tr>
                                </tbody>
                            </table>
                            <div class="row">
                                <div class="col-md-8 col-xs-7 payment-method last-item-row sub_c">
                                    <div id="load_instruction" class="col-md-6 col-lg-12 mg-t-10 mg-lg-t-0-force">
                                    </div>
                                    <button type="button" class="btn btn-success" aria-label="Left Align" id="addqproduct">
                                        <i class="fa fa-plus-square"></i> Add Equipment
                                    </button>
                                </div>
                                <div class="col-md-4 col-xs-5 invoice-block pull-right">
                                    <div class="edit-form-btn mt-2">
                                        {{ link_to_route('biller.djcs.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                        {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-md']) }}
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                            </div>
                        </div>                        
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include("focus.modal.customer")
@endsection
@section('extra-scripts')

<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>

<script type="text/javascript">
    $(function() {
        editor();
    });

    $('#create-product').on('submit', function(e) {
        e.preventDefault();
        console.log('formData=> ', $(this).serializeArray());
    })

    // initialize datepicker with parsed php date
    const now = "{{ date('Y-m-d') }}"
    $('[data-toggle="datepicker"]')
        .datepicker({ format: "{{config('core.user_date_format')}}"})
        .datepicker('setDate', new Date(now));

    // billtype
    var billtype = $('#billtype').val();

    $('#addqproduct').on('click', function() {
        var cvalue = parseInt($('#ganak').val()) + 1;
        var nxt = parseInt(cvalue);
        $('#ganak').val(nxt);
        var functionNum = "'" + cvalue + "'";
        count = $('#saman-row div').length;

        //project details
        var project_id = $('#project_id option:selected').val();

        if (project_id = "") {
            var customer_id = "";
            var branch_id = "";
            var project_description = "";
        } else {

            var customer_id = $('#project_id option:selected').attr('data-type1');
            var branch_id = $('#project_id option:selected').attr('data-type2');
            var project_description = $('#project_id option:selected').attr('data-type3');
        }

        //product row
        var data = (
            '<tr><td><input type="text" class="form-control required"  required="required" name="tag_number[]" placeholder="Search Equipment" id="tag_number-' +
            cvalue +
            '" autocomplete="off"></td><td><input type="text" class="form-control req amnt" name="joc_card[]" id="joc_card-' +
            cvalue +
            '" autocomplete="off" ></td><td><input type="text" class="form-control req prc" name="equipment_type[]" id="equipment_type-' +
            cvalue +
            '"autocomplete="off"> </td><td><input type="text" class="form-control r" name="make[]" id="make-' +
            cvalue +
            '" autocomplete="off"></td><td><input type="text" class="form-control req" name="capacity[]" id="capacity-' +
            cvalue +
            '" autocomplete="off" ></td><td><input type="text" class="form-control req" name="location[]" id="location-' +
            cvalue +
            '" autocomplete="off" ></td><td><input type="text" class="form-control req" name="last_service_date[]" id="last_service_date-' +
            cvalue + '" autocomplete="off" data-toggle-' + cvalue +
            '="datepicker"></td><td><input type="text" class="form-control req" name="next_service_date[]" id="next_service_date-' +
            cvalue + '" autocomplete="off" data-toggle-' + cvalue +
            '="datepicker"></td><td class="text-center"><div class="dropdown"><button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action</button><div class="dropdown-menu" aria-labelledby="dropdownMenuButton"><a class="dropdown-item removeProd" href="javascript:void(0);" data-rowid="' +
            cvalue +
            '" >Remove</a><a class="dropdown-item up" href="javascript:void(0);">Up</a><a class="dropdown-item down" href="javascript:void(0);">Down</a></div></div></td></tr>'
        );

        $('tr.last-item-row').before(data);
        $('[data-toggle-' + cvalue + '="datepicker"]').datepicker('setDate', new Date(now));

        editor();
        row = cvalue;

        $('#tag_number-' + cvalue).autocomplete({
            source: function(request, response) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: baseurl + 'equipments/search/' + billtype,
                    dataType: "json",
                    method: 'post',
                    data: 'keyword=' + request.term + '&type=product_list&row_num=1&client_id=' + $("#client_id").val(),
                    success: function(data) {
                        response($.map(data, function(item) {
                            return {
                                label: item.customer + ' ' + item.name + ' ' +
                                    item.make_type + ' ' + item.capacity + ' ' +
                                    item.location,
                                value: item.name,
                                data: item
                            };
                        }));
                    }
                });
            },
            autoFocus: true,
            minLength: 0,
            select: function(event, ui) {
                id_arr = $(this).attr('id');
                id = id_arr.split("-");

                //$('#amount-0').val(1);
                $('#equipment_type-' + id[1]).val(ui.item.data.unit_type);
                $('#make-' + id[1]).val(ui.item.data.make_type);
                $('#capacity-' + id[1]).val(ui.item.data.capacity);
                $('#equipment_type-' + id[1]).val(ui.item.data.unit_type);
                $('#location-' + id[1]).val(ui.item.data.location);
                $('#last_service_date-' + id[1]).val(ui.item.data.last_maint_date);
                $('#next_service_date-' + id[1]).val(ui.item.data.next_maintenance_date);
            },
            create: function(e) {
                $(this).prev('.ui-helper-hidden-accessible').remove();
            }
        });
    });

    $("#quotation").on("click", ".up,.down,.removeProd", function() {
        var row = $(this).parents("tr:first");

        if ($(this).is('.up')) {
            row.insertBefore(row.prev());
        } else if ($(this).is('.down')) {
            row.insertAfter(row.next());
        }

        if ($(this).is('.removeProd')) {
            var pidd = $(this).closest('tr').find('.item_pdIn').val();
            var retain = $(this).closest('tr').attr('data-re');
            var pqty = $(this).closest('tr').find('.item_amnt').val();
            pqty = pidd + '-' + pqty;

            if (retain) {
                $('<input>').attr({
                    type: 'hidden',
                    id: 'restock',
                    name: 'restock[]',
                    value: pqty
                }).appendTo('form');
            }

            $(this).closest('tr').remove();
            $('#d' + $(this).closest('tr').find('.item_pdIn').attr('id')).closest('tr').remove();
            $('.item_amnt').each(function(index) {
                expRowTotal(index);
                expBillUpyog();
            });

            return false;
        }
    });


    $('#addqtitle').on('click', function() {
        var cvalue = parseInt($('#ganak').val()) + 1;
        var nxt = parseInt(cvalue);
        $('#ganak').val(nxt);
        var functionNum = "'" + cvalue + "'";
        count = $('#saman-row div').length;

        //project details

        //product row
        var data = '<tr><td><input type="text" class="form-control" name="numbering[]"id="numbering-' + cvalue +
            '" autocomplete="off" ></td> <td colspan="6"><input type="text"  class="form-control" name="product_name[]" placeholder="Enter Title Or Heading " titlename-' +
            cvalue +
            '"></td><td class="text-center"><div class="dropdown"><button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action</button><div class="dropdown-menu" aria-labelledby="dropdownMenuButton"><a class="dropdown-item removeProd" href="javascript:void(0);" data-rowid="' +
            cvalue +
            '" >Remove</a><a class="dropdown-item up" href="javascript:void(0);">Up</a><a class="dropdown-item down" href="javascript:void(0);">Down</a></div></div></td><input type="hidden" name="item_or_title[]" id="item_or_title-' +
            cvalue + '" value="1"> <input type="hidden" name="a_type[]" id="a_type-' + cvalue +
            '" value="2"><input type="hidden" name="product_id[]" id="product_id-' + cvalue +
            '" value="0"><input type="hidden" name="product_qty[]" id="product_qty-' + cvalue +
            '" value="0"><input type="hidden" name="product_subtotal[]" id="product_subtotal-' + cvalue +
            '" value="0"><input type="hidden" name="product_price[]" id="product_price-' + cvalue +
            '" value="0"><input type="hidden" name="total_tax[]" id="total_tax-' + cvalue +
            '" value="0"><input type="hidden" name="total_discount[]" id="total_discount-' + cvalue +
            '" value="0"><input type="hidden" name="product_exclusive[]" id="total_discount-' + cvalue +
            '" value="0"></tr>';
        //ajax request
        // $('#saman-row').append(data);
        $('tr.last-item-row').before(data);
        editor();
        row = cvalue;

        // $('#productname-' + cvalue).autocomplete({
        //     source: function(request, response) {
        //         $.ajaxSetup({
        //             headers: {
        //                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //             }
        //         });
        //         $.ajax({
        //             url: baseurl + 'products/search/' + billtype,
        //             dataType: "json",
        //             method: 'post',
        //             data: 'keyword=' + request.term + '&type=product_list&row_num=' + row +
        //                 '&wid=' + $("#s_warehouses option:selected").val() + '&serial_mode=' +
        //                 $("#serial_mode:checked").val(),
        //             success: function(data) {
        //                 response($.map(data, function(item) {
        //                     return {
        //                         label: item.name,
        //                         value: item.name,
        //                         data: item
        //                     };
        //                 }));
        //             }
        //         });
        //     },
        //     autoFocus: true,
        //     minLength: 0,
        //     select: function(event, ui) {
        //         id_arr = $(this).attr('id');
        //         id = id_arr.split("-");
        //         var t_r = ui.item.data.taxrate;
        //         var custom = accounting.unformat($("#taxFormat option:selected").val(), accounting
        //             .settings.number.decimal);
        //         if (custom > 0) {
        //             t_r = custom;
        //         }
        //         var discount = ui.item.data.disrate;
        //         var dup;
        //         var custom_discount = $('#custom_discount').val();
        //         if (custom_discount > 0) discount = deciFormat(custom_discount);
        //         $('.pdIn').each(function() {
        //             if ($(this).val() == ui.item.data.id) dup = true;
        //         });
        //         if (dup) {
        //             alert('Already Exists!!');
        //             return;
        //         }
        //         $('#amount-' + id[1]).val(1);
        //         $('#price-' + id[1]).val(accounting.formatNumber(ui.item.data.price));
        //         $('#pid-' + id[1]).val(ui.item.data.id);
        //         $('#vat-' + id[1]).val(accounting.formatNumber(t_r));
        //         $('#discount-' + id[1]).val(accounting.formatNumber(discount));

        //         $('#unit-' + id[1]).val(ui.item.data.unit).attr('attr-org', ui.item.data.unit);
        //         $('#hsn-' + id[1]).val(ui.item.data.code);
        //         $('#alert-' + id[1]).val(ui.item.data.alert);
        //         $('#serial-' + id[1]).val(ui.item.data.serial);
        //         $('#dpid-' + id[1]).summernote('code', ui.item.data.product_des);
        //         $("#project-" + id[1]).val(project_description);
        //         $("#project_id-" + id[1]).val(project_id);
        //         $("#client_id-" + id[1]).val(customer_id);
        //         $("#branch_id-" + id[1]).val(branch_id);

        //         qtRowTotal(cvalue);
        //         qtyBillUpyog();
        //         if (typeof unit_load === "function") {
        //             unit_load();
        //             $('.unit').show();
        //         }
        //     },
        //     create: function(e) {
        //         $(this).prev('.ui-helper-hidden-accessible').remove();
        //     }
        // });
    });

    $("#quotation").on("click", ".up,.down,.removeProd", function() {
        var row = $(this).parents("tr:first");
        if ($(this).is('.up')) {
            row.insertBefore(row.prev());
        } else if ($(this).is('.down')) {
            row.insertAfter(row.next());
        }
        if ($(this).is('.removeProd')) {
            var pidd = $(this).closest('tr').find('.item_pdIn').val();
            var retain = $(this).closest('tr').attr('data-re');
            var pqty = $(this).closest('tr').find('.item_amnt').val();
            pqty = pidd + '-' + pqty;
            if (retain) {
                $('<input>').attr({
                    type: 'hidden',
                    id: 'restock',
                    name: 'restock[]',
                    value: pqty
                }).appendTo('form');
            }
            $(this).closest('tr').remove();
            $('#d' + $(this).closest('tr').find('.item_pdIn').attr('id')).closest('tr').remove();
            $('.item_amnt').each(function(index) {
                expRowTotal(index);
                expBillUpyog();
            });
            return false;
        }
    });

    $("#person").select2({
        tags: [],
        ajax: {
            url: "{{route('biller.customers.select')}}",
            dataType: 'json',
            type: 'POST',
            quietMillis: 50,
            data: function(person) {
                return {
                    person: person
                };
            },
            processResults: function(data) {
                return {
                    results: $.map(data, function(item) {
                        return {
                            text: item.name + ' - ' + item.company,
                            id: item.id
                        }
                    })
                };
            },
        }
    });

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $("#person").on('change', function() {
        $("#branch_id").val('').trigger('change');
        var tips = $('#person :selected').val();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $("#branch_id").select2({
            ajax: {
                url: "{{route('biller.branches.branch_load')}}?id=" + tips,
                dataType: 'json',
                type: 'POST',
                quietMillis: 50,
                params: {
                    'cat_id': tips
                },
                data: function(product) {
                    return {
                        product: product
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


    $("#lead_id").change(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: "POST",
            url: baseurl + 'leads/lead_search',
            data: 'keyword=' + $(this).val(),
            success: function(data) {
                $("#subject").val(data.note);
                $("#client_id").val(data.client_id);
                $("#branch_id").val(data.branch_id);


            }
        });
    });

    $('#tag_number-0').autocomplete({
        source: function(request, response) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // fetch equipments
            console.log('equipment_url=', baseurl + 'equipments/search/' + billtype)
            $.ajax({
                url: baseurl + 'equipments/search/' + billtype,
                dataType: "json",
                method: 'post',
                data: 'keyword=' + request.term + '&type=product_list&row_num=1&client_id=' + $("#client_id").val(),
                success: function(data) {
                    console.log('data_',data);
                    response($.map(data, function(item) {
                        // console.log(item);
                        return {
                            label: item.customer + ' ' + item.name + ' ' + item
                                .make_type + ' ' + item.capacity + ' ' + item.location,
                            value: item.name,
                            data: item
                        };
                    }));
                }
            });
        },
        autoFocus: true,
        minLength: 0,
        select: function(event, ui) {
            //$('#amount-0').val(1);
            $('#equipment_type-0').val(ui.item.data.unit_type);
            $('#make-0').val(ui.item.data.make_type);
            $('#capacity-0').val(ui.item.data.capacity);
            $('#equipment_type-0').val(ui.item.data.unit_type);
            $('#location-0').val(ui.item.data.location);
            $('#last_service_date-0').val(ui.item.data.last_maint_date);
            $('#next_service_date-0').val(ui.item.data.next_maintenance_date);
        }
    });

    var qtRowTotal = function(numb) {
        //most res
        var result;
        var page = '';
        var totalValue = 0;
        var amountVal = accounting.unformat($("#amount-" + numb).val(), accounting.settings.number.decimal);

        var priceVal = accounting.unformat($("#price-" + numb).val(), accounting.settings.number.decimal);

        var discountVal = accounting.unformat($("#discount-" + numb).val(), accounting.settings.number.decimal);
        var vatVal = accounting.unformat($("#vat-" + numb).val(), accounting.settings.number.decimal);
        var taxo = 0;
        var disco = 0;
        var totalPrice = amountVal.toFixed(two_fixed) * priceVal;

        var tax_status = $("#taxFormat option:selected").attr('data-type2');
        var disFormat = $("#discount_format").val();
        if ($("#inv_page").val() == 'new_i' && formInputGet("#pid", numb) > 0) {
            var alertVal = accounting.unformat($("#alert-" + numb).val(), accounting.settings.number.decimal);
            if (alertVal > 0 && alertVal <= +amountVal) {
                var aqt = alertVal - amountVal;
                alert('Low Stock! ' + accounting.formatNumber(aqt));
            }
        }

        var custom = accounting.unformat($("#taxFormat option:selected").val(), accounting.settings.number.decimal);
        if (tax_status == 'exclusive') {
            var Inpercentage = precentCalc(totalPrice, custom); //tax amount per row
            totalValue = totalPrice + Inpercentage;

            var priceInc = priceVal + Inpercentage;

            taxo = accounting.formatNumber(Inpercentage);
        } else if (tax_status == 'inclusive') {
            // 
        }

        //console.log(priceVal);
        if (priceVal < 0) {
            disco = priceInc * -1;
        }

        $("#result-" + numb).html(accounting.formatNumber(totalValue));
        $("#taxa-" + numb).val(taxo);
        $("#rate_inclusive-" + numb).val(accounting.formatNumber(priceInc));

        $("#texttaxa-" + numb).text(taxo);
        $("#disca-" + numb).val(disco);
        $("#total-" + numb).val(accounting.formatNumber(totalValue));

        $("#totalinc-" + numb).val(accounting.formatNumber(totalPrice));
        qtyBillUpyog();
    };

    var qtyBillUpyog = function() {
        var out = 0;
        var disc_val = accounting.unformat($("#discs").val(), accounting.settings.number.decimal);
        console.log(disc_val);

        if (disc_val) {
            out = accounting.unformat(disc_val, accounting.settings.number.decimal);
            out = parseFloat(out).toFixed(two_fixed);

            $('#disc_final').html(accounting.formatNumber(out));
            $('#after_disc').val(accounting.formatNumber(out));
        } else {
            $('#disc_final').html(0);
        }

        var totalBillVal = accounting.formatNumber(qtySamanYog() + shipTot() - coupon() - out);
        $("#mahayog").html(totalBillVal);
        $("#subttlform").val(accounting.formatNumber(qtySamanYog()));
        $("#invoiceyoghtml").val(totalBillVal);
        $("#bigtotal").html(totalBillVal);
        $('#keyword').val('');
    };

    //product total
    var qtySamanYog = function() {
        var itempriceList = [];
        var idList = [];
        var r = 0;
        $('.ttInput').each(function() {
            var vv = accounting.unformat($(this).val(), accounting.settings.number.decimal);
            var vid = $(this).attr('id');
            vid = vid.split("-");
            itempriceList.push(vv);
            idList.push(vid[1]);
            r++;
        });

        var sum = 0;
        var taxc = 0;
        var discs = 0;
        var totalInc = 0;
        for (var z = 0; z < idList.length; z++) {
            var x = idList[z];
            if (itempriceList[z] > 0) {
                sum += itempriceList[z];
            }
            var t1 = accounting.unformat($("#taxa-" + x).val(), accounting.settings.number.decimal);
            var d1 = accounting.unformat($("#disca-" + x).val(), accounting.settings.number.decimal);
            var d2 = accounting.unformat($("#totalinc-" + x).val(), accounting.settings.number.decimal);
            //if (t1 > 0) {
            taxc += t1;
            //  }
            if (d1 > 0) {
                discs += d1;
            }

            if (d2 > 0) {
                totalInc += d2;
            }
        }
        $("#discs").val(accounting.formatNumber(discs));
        $("#taxr").val(accounting.formatNumber(taxc));
        $("#exclusive").val(accounting.formatNumber(totalInc));

        return accounting.unformat(sum, accounting.settings.number.decimal);
    };
</script>
@endsection