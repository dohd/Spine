@extends ('core.layouts.app')

@section ('title', ' Diagnosis Job Card | Edit Diagnosis Job Card')

@section('page-header')
<h1>
    Diagnosis Job Card<small>Edit Diagnosis Job Card</small>
</h1>
@endsection

@section('content')
<div class="">
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-2">
                <h4 class=" mb-0">Djc Report Management</h4>

            </div>
            <div class="content-header-right col-md-6 col-12">
                <div class="media width-250 float-right">

                    <div class="media-body media-right text-right">
                        @include('focus.djcs.partials.djcs-header-buttons')
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="card">
                <div class="card-content">
                    <div class="card-body">
                        {{ Form::model($djc, ['route' => ['biller.djcs.update', $djc], 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'PATCH', 'id' => 'edit-djc']) }}

                        <div class="row">
                            <div class="col-sm-6 cmp-pnl">
                                <div id="customerpanel" class="inner-cmp-pnl">
                                    <div class="form-group row">
                                        <div class="fcol-sm-12">
                                            <h3 class="title pl-1"> Djc Details</h3>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-sm-12"><label for="ref_type" class="caption">Search Lead </label>
                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                                <select class="form-control  round  select-box" name="lead_id" id="lead_id" data-placeholder="{{trans('tasks.assign')}}" required="required">
                                                    @foreach ($leads as $lead)
                                                        @php
                                                            if ($lead->client_status == "customer") {
                                                                $name = $lead->customer->company.' '. $lead->branch->name;
                                                            } else {
                                                                $name = $lead->client_name;
                                                            }
                                                        @endphp
                                                        <option value="{{ $lead->id }}">{{$lead->reference}} - {{$name}} - {{$lead->title}}</option>
                                                    @endforeach
                                                </select>
                                                <input type="hidden" name="client_id" id="client_id" value="{{ $djc->client_id }}">
                                                <input type="hidden" name="branch_id" id="branch_id" value="{{ $djc->branch_id }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <label for="attention" class="attention">Attention</label>
                                            {{ Form::text('attention', null, ['class' => 'form-control round required', 'placeholder' => 'Atrtention','autocomplete'=>'false','id'=>'attention']) }}
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
                                        <div class="col-sm-4"><label for="report_date" class="caption">Report {{trans('general.date')}}</label>
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
                                        <div class="col-sm-4"><label for="prepared_by" class="caption">Prepaired By</label>
                                            <div class="input-group">
                                                <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span>
                                                </div>
                                                {{ Form::text('prepared_by', null, ['class' => 'form-control round', 'placeholder' => 'Prepaired By']) }}
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <label for="technician" class="caption"> Techinican</label>
                                            {{ Form::text('technician', null, ['class' => 'form-control round required', 'placeholder' => 'Technician','autocomplete'=>'false','id'=>'prepaired_by','required' => 'required']) }}
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
                                                {{ Form::text('caption_three', null, ['class' => 'form-control round ', 'placeholder' => 'Caption 4','id'=>'caption_three']) }}
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
                                            {{ Form::text('subject', null, ['class' => 'form-control round required', 'placeholder' => 'Subject / Title','autocomplete'=>'false','id'=>'subject']) }}
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
                            <table id="equipment" class="table-responsive tfr my_stripe_single" style="padding-bottom: 100px;">
                                <thead>
                                    <tr class="item_header bg-gradient-directional-blue white">
                                        <th width="20%" class="text-center">Tag/Unique Number</th>
                                        <th width="10%" class="text-center">Jobcard</th>
                                        <th width="10%" class="text-center">Type</th>
                                        <th width="10%" class="text-center">Make</th>
                                        <th width="10%" class="text-center">Capacity</th>
                                        <th width="10%" class="text-center">Location</th>
                                        <th width="10%" class="text-center">Last Service Date</th>
                                        <th width="10%" class="text-center">Next Service date</th>
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
                                    <div id="load_instruction" class="col-md-6 col-lg-12 mg-t-10 mg-lg-t-0-force"></div>
                                    <button type="button" class="btn btn-success" aria-label="Left Align" id="addqproduct">
                                        <i class="fa fa-plus-square"></i> Add Equipment
                                    </button>
                                </div>
                                <div class="col-md-4 col-xs-5 invoice-block pull-right">
                                    <div class="edit-form-btn mt-2">
                                        {{ link_to_route('biller.djcs.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                        {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md']) }}
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
@inclue("focus.djcs.edit")

<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>

<script type="text/javascript">
    $('#edit-djc').submit(function(e) {
        e.preventDefault();
        console.log('serialized => ', $(this).serializeArray());
    });

    // initialize date picker with php parsed date
    const now = "{{ date('Y-m-d') }}";
    $('[data-toggle="datepicker"]')
        .datepicker({format: "{{config('core.user_date_format')}}"})
        .datepicker('setDate', new Date(now));

    // initialize html editor
    editor();

    // ajax setup
    $.ajaxSetup({ 
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
    });

    // equipment row counter;
    var counter = 1;

    // autocomplete properties
    const autocompleteProp = {
        source: function(request, response) {
            const billtype = counter;
            $.ajax({
                url: baseurl + 'equipments/search/' + billtype,
                dataType: "json",
                method: 'post',
                data: 'keyword=' + request.term + '&type=product_list&row_num=1&client_id=' + $("#client_id").val(),
                success: function(data) {
                    console.log('auto_data', data)
                    response($.map(data, function(item) {
                        // console.log(item);
                        return {
                            label: item.customer + ' ' + item.name + ' ' + item.make_type + ' ' + item.capacity + ' ' + item.location,
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
            $('#equipment_type-0').val(ui.item.data.unit_type);
            $('#make-0').val(ui.item.data.make_type);
            $('#capacity-0').val(ui.item.data.capacity);
            $('#equipment_type-0').val(ui.item.data.unit_type);
            $('#location-0').val(ui.item.data.location);
            $('#last_service_date-0').val(ui.item.data.last_maint_date);
            $('#next_service_date-0').val(ui.item.data.next_maintenance_date);
        }
    };

    // initial autocomplete on default equipment row
    $('#tag_number-0').autocomplete(autocompleteProp);
    
    // on clicking add product/equipment row
    $('#addqproduct').on('click', function() {
        const cvalue = counter++;

        // product or equipment row html
        var data = (
            '<tr><td><input type="text" class="form-control required"  required="required" name="tag_number[]" placeholder="Search Equipment" id="tag_number-' 
            + cvalue + '" autocomplete="off"></td><td><input type="text" class="form-control req amnt" name="joc_card[]" id="joc_card-' 
            + cvalue + '" autocomplete="off" ></td><td><input type="text" class="form-control req prc" name="equipment_type[]" id="equipment_type-' 
            + cvalue + '"autocomplete="off"> </td><td><input type="text" class="form-control r" name="make[]" id="make-' 
            + cvalue + '" autocomplete="off"></td><td><input type="text" class="form-control req" name="capacity[]" id="capacity-' 
            + cvalue + '" autocomplete="off" ></td><td><input type="text" class="form-control req" name="location[]" id="location-' 
            + cvalue + '" autocomplete="off" ></td><td><input type="text" class="form-control req" name="last_service_date[]" id="last_service_date-' 
            + cvalue + '" autocomplete="off" data-toggle-' 
            + cvalue + '="datepicker"></td><td><input type="text" class="form-control req" name="next_service_date[]" id="next_service_date-' 
            + cvalue + '" autocomplete="off" data-toggle-' 
            + cvalue + '="datepicker"></td><td class="text-center"><div class="dropdown"><button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action</button><div class="dropdown-menu" aria-labelledby="dropdownMenuButton"><a class="dropdown-item removeProd" href="javascript:void(0);" data-rowid="' 
            + cvalue + '" >Remove</a><a class="dropdown-item up" href="javascript:void(0);">Up</a><a class="dropdown-item down" href="javascript:void(0);">Down</a></div></div></td></tr>'
        );

        // append row to html tr tag 
        $('tr.last-item-row').before(data);

        // initialize datepicker
        $('[data-toggle-' + cvalue + '="datepicker"]')
            .datepicker({format: "{{config('core.user_date_format')}}"})
            .datepicker('setDate', new Date(now));

        // autocomplete on added row
        $('#tag_number-' + cvalue).autocomplete(autocompleteProp);
    });

    // on clicking equipment drop down options
    $("#equipment").on("click", ".up,.down,.removeProd", function() {
        var row = $(this).parents("tr:first");
        // move row up on click up
        if ($(this).is('.up')) row.insertBefore(row.prev());
        // move row down on click down
        if ($(this).is('.down')) row.insertAfter(row.next());
        // remove row on click remove
        if ($(this).is('.removeProd')) $(this).closest('tr').remove();
    });
    
    // on selecting lead option, fetch lead details from the server
    $("#lead_id").on('change', function() {
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
</script>
@endsection