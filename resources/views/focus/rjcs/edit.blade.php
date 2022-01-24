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
                <h4 class="content-header-title">Rjc Report Management</h4>
            </div>
            <div class="content-header-right col-md-6 col-12">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        @include('focus.rjcs.partials.rjcs-header-buttons')
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="card">
                <div class="card-content">
                    <div class="card-body">
                        {{ Form::model($rjc, ['route' => ['biller.rjcs.update', $rjc], 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'PATCH', 'id' => 'edit-rjc']) }}
                        <div class="row">
                            <div class="col-sm-6 cmp-pnl">
                                <div id="customerpanel" class="inner-cmp-pnl">
                                    <div class="form-group row">
                                        <div class="fcol-sm-12">
                                            <h3 class="title pl-1"> Rjc Details</h3>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-12"><label for="ref_type" class="caption">Search Ticket </label>
                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                                <select class="form-control  round  select-box" name="project_id" id="project" required="required">
                                                    <option value="">-- Select Project --</option>
                                                    @foreach ($projects as $project)
                                                        <option value="{{ $project->id }}" {{ ($rjc->project->id == $project->id) ? 'selected' : '' }}>
                                                            {{ 'Prj-'.sprintf('%04d', $project->project_number) }} - {{ $project->name }}
                                                            {{ $project->quote_tids }}
                                                        </option>                                                        
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-12">
                                            <label for="attention" class="attention">Attention <span class="text-danger">*</span></label>
                                            {{ Form::text('attention', null, ['class' => 'form-control round required', 'placeholder' => 'Attention','autocomplete'=>'false','id'=>'attention']) }}
                                        </div>                                        
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-4"><label for="tid" class="caption">Report No</label>
                                            <div class="input-group">
                                                <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span>
                                                </div>
                                                {{ Form::text('tid', 'RjR-'.sprintf('%04d', $rjc->tid), ['class' => 'form-control round', 'disabled']) }}
                                                <input type="hidden" name="tid" value="{{ $rjc->tid }}">
                                            </div>
                                        </div>
                                        <div class="col-sm-4"><label for="report_date" class="caption">Report {{trans('general.date')}}</label>
                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span>
                                                </div>
                                                {{ Form::text('report_date', null, ['class' => 'form-control round required', 'placeholder' => trans('general.date'),'data-toggle'=>'datepicker','autocomplete'=>'false']) }}
                                            </div>
                                        </div>
                                        <div class="col-sm-4"><label for="reference" class="caption">Client Ref / Callout ID</label>
                                            <div class="input-group">
                                                <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span>
                                                </div>
                                                {{ Form::text('client_ref', null, ['class' => 'form-control round ', 'placeholder' => 'Client Ref', 'id' => 'client_ref', 'required']) }}
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
                                <div class="col-5">
                                    {{ Form::submit(trans('buttons.general.crud.update') . ' Report', ['class' => 'btn btn-primary btn-lg mt-3 float-right']) }}
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

@section('extra-scripts')
<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>

<script type="text/javascript">
    // initialize html editor
    editor();

    // initialize datepicker
    $('[data-toggle="datepicker"]')
        .datepicker({format: "{{config('core.user_date_format')}}"})
        .datepicker('setDate', new Date("{{ $rjc->report_date }}"));

    // product (equipment) row
    function equipmentRow(cvalue) {
        return `
            <tr>
                <td><input type="text" class="form-control required"  required="required" name="tag_number[]" placeholder="Search Equipment" id="tag_number-${cvalue}" autocomplete="off"></td>
                <td><input type="text" class="form-control req amnt" name="joc_card[]" id="joc_card-${cvalue}" autocomplete="off"></td>
                <td><input type="text" class="form-control req prc" name="equipment_type[]" id="equipment_type-${cvalue}" autocomplete="off"></td>
                <td><input type="text" class="form-control r" name="make[]" id="make-${cvalue}" autocomplete="off"></td>
                <td><input type="text" class="form-control req" name="capacity[]" id="capacity-${cvalue}" autocomplete="off"></td>
                <td><input type="text" class="form-control req" name="location[]" id="location-${cvalue}" autocomplete="off"></td>
                <td><input type="text" class="form-control req" name="last_service_date[]" id="last_service_date-${cvalue}" autocomplete="off" data-toggle="datepicker"></td>
                <td><input type="text" class="form-control req" name="next_service_date[]" id="next_service_date-${cvalue}" autocomplete="off" data-toggle="datepicker"></td>
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
        $('#equipment tr').each(function(i) {
            if (!i) return;
            const index = $(this).index();
            $(this).find('input[name="row_index[]"]').val(index);
        });
    }

    // equipment row counter;
    var counter = 1;
    // set default rjc items rows
    const rjcItems = @json($items);
    rjcItems.forEach(v => {
        const i = counter;
        // add poduct row to equipment table
        const row = equipmentRow(i);
        $('#equipment tr:last').after(row);
        // initialize date picker with php parsed date
        $('[data-toggle="datepicker"]')
            .datepicker({format: "{{config('core.user_date_format')}}"});
        $('#tag_number-' + i).autocomplete(autocompleteProp(i));

        // default input with values
        $('#itemid-'+i).val(v.id);
        $('#tag_number-'+i).val(v.tag_number);
        $('#joc_card-'+i).val(v.joc_card);
        $('#equipment_type-'+i).val(v.equipment_type);
        $('#make-'+i).val(v.make);
        $('#capacity-'+i).val(v.capacity);
        $('#location-'+i).val(v.location);
        $('#last_service_date-'+i).val(v.last_service_date);
        $('#next_service_date-'+i).val(v.next_service_date);

        assignIndex();
        counter++;
    });

    // ajax setup
    $.ajaxSetup({ 
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
    });

    // autocompleteProp returns autocomplete object properties
    function autocompleteProp(i = 0) {
        return {
            source: function(request, response) {
                const billtype = counter;
                $.ajax({
                    url: baseurl + 'equipments/search/' + billtype,
                    dataType: "json",
                    method: 'post',
                    data: 'keyword=' + request.term + '&type=product_list&row_num=1&client_id=' + $("#client_id").val(),
                    success: function(data) {
                        response($.map(data, function(itm) {
                            const label = `${itm.customer} ${itm.name} ${itm.make_type} ${itm.capacity} ${itm.location}`
                            const value = itm.name;
                            const data = itm;
                            return { label, value, data};
                        }));
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

    // on clicking addproduct (equipment) button
    $('#addqproduct').on('click', function() {
        const cvalue = counter++;
        // product (equipment) row
        const row = equipmentRow(cvalue);
        // add poduct row to equipment table
        $('#equipment tr:last').after(row);
        // initialize date picker with php parsed date
        $('[data-toggle="datepicker"]')
            .datepicker({format: "{{config('core.user_date_format')}}"})
            .datepicker('setDate', new Date());
        // add jobcard
        $('#joc_card-' + cvalue).val($('#jobcard').val());
        // autocomplete on added product row
        $('#tag_number-' + cvalue).autocomplete(autocompleteProp(cvalue));

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
        if ($(this).is('.removeProd')) {
            const response = window.confirm('Are you sure to delete this item ?');
            if (response) {
                const row = $(this).closest('tr');
                row.remove();
                const itemId = row.find('input[name="item_id[]"]').val();
                // delete item api call 
                $.ajax({
                    url: baseurl + 'rjcs/delete_item/' + itemId,
                    dataType: "json",
                    method: 'DELETE',
                });
            }
        }

        assignIndex();
    });
</script>
@endsection
