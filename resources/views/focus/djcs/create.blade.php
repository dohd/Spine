@extends ('core.layouts.app')

@section ('title', ' Diagnosis Job Card | Create Diagnosis Job Card')

@section('page-header')
<h1>
    Diagnosis Job Card<small>Create Diagnosis Job Card</small>
</h1>
@endsection

@section('content')
<div class="">
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-2">
                <h4 class="content-header-title">Djc Report Management</h4>
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
                        {{ Form::open(['route' => 'biller.djcs.store', 'class' => 'form-horizontal', 'method' => 'POST', 'files' => true ]) }}
                        <div class="row">
                            <div class="col-sm-6">
                                <div>
                                    <div class="form-group row">
                                        <div class="col-12">
                                            <h3>Djc Details</h3>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-sm-12"><label for="ref_type" class="caption">Ticket </label>
                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                                <select class="form-control  round  select-box" name="lead_id" id="lead_id" required>
                                                    <option value="">-- Select Ticket --</option>
                                                        @foreach ($leads as $lead)
                                                            @php
                                                                $name = $lead->client_name;
                                                                $tid = 'Tkt-'.sprintf('%04d', $lead->reference);
                                                                if ($lead->client_status == "customer") {
                                                                    $name = $lead->customer->company.' - '. $lead->branch->name;
                                                                }
                                                            @endphp
                                                            <option value="{{ $lead->id }}">
                                                                {{ $tid }} - {{ $name }} - {{ $lead->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>                                                
                                                <input type="hidden" name="client_id" value="0" id="client_id">
                                                <input type="hidden" name="branch_id" value="0" id="branch_id">                                                
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-5">
                                            <label for="attention" class="attention">Attention <span class="text-danger">*</span></label>
                                            {{ Form::text('attention', null, ['class' => 'form-control round required', 'placeholder' => 'Attention','autocomplete'=>'false','id'=>'attention']) }}
                                        </div>
                                        <div class="col-sm-4">
                                            <label for="jobcard" class="jobcard">Job Card</label>
                                            <div class="input-group">
                                                <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span></div>
                                                {{ Form::text('job_card', null, ['class' => 'form-control round required', 'placeholder' => 'Job Card', 'id'=>'jobcard']) }}
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <label for="jobcard" class="jobcard">Job Card Date</label>
                                            <div class="input-group">
                                                {{ Form::text('jobcard_date', null, ['class' => 'form-control', 'data-toggle'=>'datepicker', 'id'=>'jobcard_date']) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-4"><label for="tid" class="caption">Report No</label>
                                            <div class="input-group">
                                                <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span></div>
                                                {{ Form::text('tid', 'DjR-' . sprintf('%04d', $tid), ['class' => 'form-control round', 'disabled']) }}
                                                <input type="hidden" name="tid" value="{{ $tid }}">
                                            </div>
                                        </div>                                        
                                        <div class="col-sm-4"><label for="report_date" class="caption">Report {{trans('general.date')}}</label>
                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                                                {{ Form::text('report_date', null, ['class' => 'form-control round', 'data-toggle'=>'datepicker']) }}
                                            </div>
                                        </div>
                                        <div class="col-sm-4"><label for="reference" class="caption">Client Ref / Callout ID</label>
                                            <div class="input-group">
                                                <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span></div>
                                                {{ Form::text('client_ref', null, ['class' => 'form-control round ', 'id' => 'client_ref', 'required']) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-4">
                                            <label for="region" class="caption"> Region</label>
                                            {{ Form::text('region', null, ['class' => 'form-control round ', 'placeholder' => 'Region','autocomplete'=>'false','id'=>'region']) }}
                                        </div>
                                        <div class="col-sm-4"><label for="prepared_by" class="caption">Prepared By <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span></div>
                                                {{ Form::text('prepared_by', null, ['class' => 'form-control round', 'placeholder' => 'Prepared By']) }}
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <label for="technician" class="caption"> Technician <span class="text-danger">*</span></label>
                                            {{ Form::text('technician', null, ['class' => 'form-control round required', 'placeholder' => 'Technician','autocomplete'=>'false','id'=>'prepaired_by','required' => 'required']) }}
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-sm-3"><label for="client_name" class="caption"> Image 1 </label>
                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                                                {!! Form::file('image_one', array('class'=>'input', 'id'=>'image_one' )) !!}
                                            </div>
                                        </div>
                                        <div class="col-sm-3"><label for="client_email" class="caption"> Image 2</label>
                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                                                {!! Form::file('image_two', array('class'=>'input', 'id'=>'image_two' )) !!}
                                            </div>
                                        </div>
                                        <div class="col-sm-3"><label for="client_email" class="caption"> Image 3</label>
                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                                                {!! Form::file('image_three', array('class'=>'input', 'id'=>'image_three' )) !!}
                                            </div>
                                        </div>
                                        <div class="col-sm-3"><label for="client_email" class="caption"> Image 4</label>
                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                                                {!! Form::file('image_four', array('class'=>'input', 'id'=>'image_four' )) !!}
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
                            <div class="col-sm-6">
                                <div class="inner-cmp-pnl">
                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <h3 class="subject">Report</h3>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <label for="subject" class="caption">Subject / Title <span class="text-danger">*</span></label>
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

                        <div>
                            <table id="equipment" class="table-responsive tfr my_stripe_single">
                                <thead>
                                    <tr class="item_header bg-gradient-directional-blue white">
                                        <th width="20%" class="text-center">Tag/Unique Number</th>
                                        <th width="10%" class="text-center">Jobcard</th>
                                        <th width="10%" class="text-center">Type</th>
                                        <th width="10%" class="text-center">Make</th>
                                        <th width="10%" class="text-center">Capacity</th>
                                        <th width="10%" class="text-center">Location</th>
                                        <th width="10%" class="text-center">Last Service Date</th>
                                        <th width="10%" class="text-center">&nbsp;Next Service Date</th>
                                        <th width="10%" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            <div class="row mt-1">
                                <div class="col-6">
                                    <button type="button" class="btn btn-success" aria-label="Left Align" id="addqproduct">
                                        <i class="fa fa-plus-square"></i> Add Equipment
                                    </button>
                                </div>
                                <div class="col-5">
                                    {{ Form::submit(trans('buttons.general.crud.create') . ' Report', ['class' => 'btn btn-primary btn-lg mt-3 float-right']) }}
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
@endsection

@section('extra-style')
<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
@endsection

@section('extra-scripts')
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
 
<script type="text/javascript">
    // initialize html editor
    editor();

    // initialize date picker 
    $('[data-toggle="datepicker"]')
        .datepicker({format: "{{config('core.user_date_format')}}"})
        .datepicker('setDate', new Date());

    // ajax setup
    $.ajaxSetup({ 
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
    });

    // on selecting lead fetch lead details from the server
    $('#lead_id').change(function() {
        $.ajax({
            type: "POST",
            url: baseurl + 'leads/lead_search',
            data: 'keyword=' + $(this).val(),
            success: function(data) {
                $("#subject").val(data.title);
                $("#client_id").val(data.client_id);
                $("#branch_id").val(data.branch_id);
                $("#client_ref").val(data.client_ref);
            }
        });
    });

    // add jobcard value to default equipment row
    $("#jobcard").change(function() {
        $('input[name="joc_card[]"]').val($(this).val());
    });
    
    // product row
    function productRow(n) {            
        return `
            <tr>
                <td><input type="text" class="form-control"  required name="tag_number[]" placeholder="Search Equipment" id="tag_number-${n}" autocomplete="off"></td>
                <td><input type="text" class="form-control req amnt" name="joc_card[]" id="joc_card-${n}"></td>
                <td><input type="text" class="form-control req prc" name="equipment_type[]" id="equipment_type-${n}" autocomplete="off"></td>
                <td><input type="text" class="form-control r" name="make[]" id="make-${n}" autocomplete="off"></td>
                <td><input type="text" class="form-control req" name="capacity[]" id="capacity-${n}" autocomplete="off"></td>
                <td><input type="text" class="form-control req" name="location[]" id="location-${n}" autocomplete="off"></td>
                <td><input type="text" class="form-control req" name="last_service_date[]" id="last_service_date-${n}" autocomplete="off" data-toggle-${n}="datepicker"></td>
                <td><input type="text" class="form-control req" name="next_service_date[]" id="next_service_date-${n}" autocomplete="off" data-toggle-${n}="datepicker"></td>
                <td class="text-center">
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Action
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a class="dropdown-item removeProd" href="javascript:void(0);" data-rowid="${n}" >Remove</a>
                            <a class="dropdown-item up" href="javascript:void(0);">Up</a>
                            <a class="dropdown-item down" href="javascript:void(0);">Down</a>
                        </div>
                    </div>
                </td>
                <input type="hidden" name="row_index[]" value="0" id="rowindex-${n}">
            </tr>
        `;
    }

    // assign row index
    function assignIndex() {
        $('#equipment tr').each(function() {
            if (!$(this).index()) return;
            $(this).find('input[name="row_index[]"]').val($(this).index());
        });
    }

    // equipment row counter;
    let eqmntIndx = 0;
    // add default product row
    $('#equipment tr:last').after(productRow(0));
    // initialize date picker
    $('[data-toggle-0="datepicker"]')
        .datepicker({format: "{{config('core.user_date_format')}}"})
        .datepicker('setDate', new Date());
    // autocomplete on default product row
    $('#tag_number-0').autocomplete(autocompleteProp(0));
    assignIndex();
    
    // on clicking addproduct (equipment) button
    $('#addqproduct').on('click', function() {
        eqmntIndx++;
        const i = eqmntIndx;
        // add poduct row to equipment table
        const row = productRow(i);
        $('#equipment tr:last').after(row);
        // add jobcard value   
        $('input[name="joc_card[]"]').val($("#jobcard").val());     
        // initialize datepicker
        $(`[data-toggle-${i}="datepicker"]`)
            .datepicker({format: "{{config('core.user_date_format')}}"})
            .datepicker('setDate', new Date());
        // autocomplete on added product row
        $('#tag_number-' + i).autocomplete(autocompleteProp(i));
        assignIndex();
    });

    // on clicking equipment drop down options
    $("#equipment").on("click", ".up,.down,.removeProd", function() {
        var row = $(this).parents("tr:first");
        // move row up 
        if ($(this).is('.up')) row.insertBefore(row.prev());
        // move row down
        if ($(this).is('.down')) row.insertAfter(row.next());
        // remove row
        if ($(this).is('.removeProd')) $(this).closest('tr').remove();

        assignIndex();
    });

    // autocompleteProp returns autocomplete object properties
    function autocompleteProp(i) {
        console.log('auto called')
        return {
            source: function(request, response) {
                $.ajax({
                    url: baseurl + 'equipments/search/' + $("#client_id").val(),
                    dataType: "json",
                    method: 'post',
                    data: 'keyword=' + request.term + '&type=product_list&row_num=1&client_id=' + $("#client_id").val(),
                    success: function(data) {
                        const equips = data.map(v => ({
                            label: `${v.customer} ${v.name} ${v.make_type} ${v.capacity} ${v.location}`,
                            value: v.name,
                            data: v
                        }));
                        response(equips);
                    }
                });
            },
            autoFocus: true,
            minLength: 0,
            select: function(event, ui) {
                const {data} = ui.item;
                $('#equipment_type-'+i).val(data.unit_type);
                $('#make-'+i).val(data.make_type);
                $('#capacity-'+i).val(data.capacity);
                $('#location-'+i).val(data.location);
                $('#last_service_date-'+i).val(data.last_maint_date);
                $('#next_service_date-'+i).val(data.next_maintenance_date);
            }
        };
    }
    
    // check if file input has file and set caption validation to required
    const keys = ['one', 'two', 'three', 'four'];
    keys.forEach(v => {
        const imageId = '#image_'+v;
        const captionId = '#caption_'+v;
        $(imageId).on('change', function() {
            if ($(imageId).get(0).files.length > 0) {
                $(captionId).prop('required', true);
            } else {
                $(captionId).prop('required', false);
            }
        });
    });    
</script>
@endsection
