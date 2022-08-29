@extends ('core.layouts.app')

@php
    $query_str = request()->getQueryString();
    $part_title = preg_match('/page=copy/', $query_str) ? 'Copy' : 'Edit';
@endphp

@section ('title', ' Diagnosis Job Card | ' . $part_title)

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Djc Report Management</h4>
        </div>
        <div class="content-header-right col-6">
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
                    @if ($query_str == 'page=copy')
                        {{ Form::model($djc, ['route' => ['biller.djcs.store', $djc], 'method' => 'POST', 'files' => true]) }}
                    @else
                        {{ Form::model($djc, ['route' => ['biller.djcs.update', $djc], 'method' => 'PATCH', 'files' => true]) }}
                    @endif
                        <div class="row">
                            <div class="col-sm-6 cmp-pnl">
                                <div id="customerpanel" class="inner-cmp-pnl">
                                    <div class="form-group row">
                                        <div class="fcol-sm-12">
                                            <h3 class="title pl-1"> Djc Details</h3>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-12"><label for="ref_type" class="caption">Ticket </label>
                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                                <select class="form-control  round" name="lead_id" id="lead_id" required>
                                                    <option value="">-- Select Ticket --</option>
                                                    @foreach ($leads as $lead)
                                                        @php
                                                            $tid = 'Tkt-'.sprintf('%04d', $lead->reference);
                                                            $name =  isset($lead->customer) ? $lead->customer->company : $lead->client_name;
                                                            $branch = isset($lead->branch) ? $lead->branch->name : '';
                                                            if ($name && $branch) $name .= ' - ' . $branch;  
                                                        @endphp
                                                        <option 
                                                            value="{{ $lead->id }}" 
                                                            {{ $lead->id == $djc->lead_id ? 'selected' : '' }}
                                                            title="{{ $lead->title }}"
                                                            client_ref="{{ $lead->client_ref }}"
                                                            branch_id="{{ $lead->branch? $lead->branch->id : 0 }}"
                                                            client_id="{{ $lead->customer? $lead->customer->id : 0 }}"
                                                            >
                                                            {{ $tid }} - {{ $name }} - {{ $lead->title }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <input type="hidden" name="client_id" id="client_id" value="{{ $djc->client_id }}">
                                                <input type="hidden" name="branch_id" id="branch_id" value="{{ $djc->branch_id }}">
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
                                                <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span>
                                                </div>
                                                {{ Form::text('job_card', null, ['class' => 'form-control round required', 'placeholder' => 'Job Card', 'id'=>'jobcard']) }}
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <label for="jobcard" class="jobcard">Job Card Date</label>
                                            <div class="input-group">
                                                {{ Form::text('jobcard_date', null, ['class' => 'form-control datepicker', 'id' => 'jobcard_date']) }}
                                            </div>
                                        </div>
                                    </div>                                    <div class="form-group row">
                                        <div class="col-sm-4"><label for="tid" class="caption">Report No</label>
                                            <div class="input-group">
                                                <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span></div>
                                                {{ Form::text('tid', 'DjR-' . sprintf('%04d', $djc->tid), ['class' => 'form-control round', 'disabled']) }}
                                                <input type="hidden" name="tid" value="{{ $djc->tid }}">
                                            </div>
                                        </div>
                                        <div class="col-sm-4"><label for="report_date" class="caption">Report {{trans('general.date')}}</label>
                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span>
                                                </div>
                                                {{ Form::text('report_date', null, ['class' => 'form-control datepicker round', 'id' => 'report_date']) }}
                                            </div>
                                        </div>
                                        <div class="col-sm-4"><label for="reference" class="caption">Client Ref / Callout ID</label>
                                            <div class="input-group">
                                                <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span></div>
                                                {{ Form::text('client_ref', null, ['class' => 'form-control round', 'id' => 'client_ref', 'required']) }}
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
                                                <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span>
                                                </div>
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

                            <div class="col-sm-6 cmp-pnl">
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
                                <div class="col-5 mt-3">
                                @php
                                    $text = $query_str == 'page=copy' ? 'Copy Report' : 'Update Report';
                                @endphp                                
                                {{ Form::submit($text, ['class' => 'btn btn-success btn-lg float-right']) }}
                                </div>
                            </div>
                        </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    const config = {
        ajax: { 
            headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}"}
        },
        date: {format: "{{config('core.user_date_format')}}", autoHide: true},
    };


    // initialize html editor
    editor();
    // ajax setup
    $.ajaxSetup(config.ajax);
    // initialize datepicker
    $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
    $('#report_date').datepicker('setDate', new Date("{{ $djc->report_date }}"));
    $('#jobcard_date').datepicker('setDate', new Date("{{ $djc->jobcard_date }}"));
    // select2
    $('#lead_id').select2({
        allowClear: true,
        placeholder: 'Search by No, Client, Branch, Title'
    }).change(function() {
        const opt = $(this).find('option:selected');
        $("#subject").val(opt.attr('title'));
        $("#client_id").val(opt.attr('client_id'));
        $("#branch_id").val(opt.attr('branch_id'));
        $("#client_ref").val(opt.attr('client_ref'));
    });
    
    // product (equipment) row
    function equipmentRow(cvalue) {
        return `
            <tr>
                <td><input type="text" class="form-control required"  required="required" name="tag_number[]" placeholder="Search Equipment" id="tag_number-${cvalue}" autocomplete="off"></td>
                <td><input type="text" class="form-control req amnt" name="joc_card[]" id="joc_card-${cvalue}" autocomplete="off"></td>
                <td><input type="text" class="form-control req prc" name="equipment_type[]" id="equipment_type-${cvalue}" autocomplete="off"></td>
                <td><input type="text" class="form-control r" name="make[]" id="make-${cvalue}" autocomplete="off"></td>
                <td><input type="text" class="form-control req" name="capacity[]" id="capacity-${cvalue}"></td>
                <td><input type="text" class="form-control req" name="location[]" id="location-${cvalue}"></td>
                <td><input type="text" class="form-control datepicker req" name="last_service_date[]" id="last_service_date-${cvalue}"></td>
                <td><input type="text" class="form-control datepicker req" name="next_service_date[]" id="next_service_date-${cvalue}"></td>
                <td class="text-center">
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Action
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a class="dropdown-item removeProd" href="javascript:void(0);" data-rowid="${cvalue}" >Remove</a>
                            <a class="dropdown-item up" href="javascript:void(0);">Up</a>
                            <a class="dropdown-item down" href="javascript:void(0);">Down</a>
                        </div>
                    </div>
                </td>
                <input type="hidden" name="item_id[]" value="0" id="itemid-${cvalue}">
                <input type="hidden" name="row_index[]" value="0" id="rowindex-${cvalue}">
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
    var counter = 1;
    const djcItems = @json($items);
    djcItems.forEach(v => {
        const i = counter;
        // add poduct row to equipment table
        const row = equipmentRow(i);
        $('#equipment tr:last').after(row);
        $('#tag_number-' + i).autocomplete(autocompleteProp(i));

        // default input with values
        $('#itemid-'+i).val(v.id);
        $('#tag_number-'+i).val(v.tag_number);
        $('#joc_card-'+i).val(v.joc_card);
        $('#equipment_type-'+i).val(v.equipment_type);
        $('#make-'+i).val(v.make);
        $('#capacity-'+i).val(v.capacity);
        $('#location-'+i).val(v.location);
        $('#last_service_date-'+i).datepicker(config.date).datepicker('setDate', new Date(v.last_service_date));
        $('#next_service_date-'+i).datepicker(config.date).datepicker('setDate', new Date(v.next_service_date));
        assignIndex();
        counter++;
    });

    // on clicking addproduct (equipment) button
    $('#addqproduct').on('click', function() {
        const i = counter++;
        const row = equipmentRow(i);
        $('#equipment tr:last').after(row);
        $('#tag_number-' + i).autocomplete(autocompleteProp(i));

        $('#joc_card-'+i).val($("#jobcard").val());
        $('#last_service_date-'+ i).datepicker(config.date).datepicker('setDate', new Date());
        $('#next_service_date-'+ i).datepicker(config.date).datepicker('setDate', new Date());
        assignIndex();
    });

    // on clicking equipment drop down options
    $("#equipment").on("click", ".up,.down,.removeProd", function() {
        var row = $(this).parents("tr:first");
        if ($(this).is('.up')) row.insertBefore(row.prev());
        if ($(this).is('.down')) row.insertAfter(row.next());
        if ($(this).is('.removeProd')) {
            if (window.confirm('Are you sure to delete this item ?'))
            $(this).closest('tr').remove();
        }
        assignIndex();
    });
    
    // autocompleteProp returns autocomplete object properties
    function autocompleteProp(i = 0) {
        return {
            source: function(request, response) {
                $.ajax({
                    url: baseurl + 'equipments/search/' + $("#client_id").val(),
                    dataType: "json",
                    method: 'post',
                    data: {
                        keyword: request.term, 
                        client_id: $('#lead_id option:selected').attr('clientId'),
                        branch_id: $('#lead_id option:selected').attr('branchId')
                    },
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
                
                const lastDate = data.last_maintenance_date? new Date(data.last_maintenance_date) : '';
                const nextDate = data.next_maintenance_date? new Date(data.next_maintenance_date) : '';
                $('#last_service_date-'+i).datepicker('setDate', lastDate);
                $('#next_service_date-'+i).datepicker('setDate', nextDate);
            }
        };
    }
</script>
@endsection
