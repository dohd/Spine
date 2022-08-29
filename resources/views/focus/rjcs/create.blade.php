@extends ('core.layouts.app')

@section ('title', 'Repair Job Card | Create')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Rjc Report Management</h4>
        </div>
        <div class="content-header-right col-6">
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
                    {{ Form::open(['route' => 'biller.rjcs.store', 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'POST', 'files' => true ]) }}
                        <div class="row">
                            <div class="col-sm-6">
                                <div>
                                    <div class="form-group row">
                                        <div class="fcol-sm-12">
                                            <h3 class="title pl-1">Repair Job Card Details</h3>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-sm-12"><label for="ref_type" class="caption">Search Project </label>
                                            <div class="input-group">
                                                <select class="form-control  round  select-box" name="project_id" id="project" required>
                                                    @foreach ($projects as $project)
                                                        <option value="{{ $project->id }}">                                                       
                                                            {{ gen4tid('Prj-', $project->tid) }} 
                                                            [ {{ $project->quote_tids }} ] [ {{ $project->lead_tids }} ]                                                         
                                                            {{ $project->name }}
                                                        </option>
                                                    @endforeach
                                                </select>                                                
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-12">
                                            <label for="attention" class="attention">Attention <span class="text-danger">*</span></label>
                                            {{ Form::text('attention', null, ['class' => 'form-control round', 'placeholder' => 'Attention', 'id'=>'attention']) }}
                                        </div>                                        
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-4"><label for="tid" class="caption">Report No</label>
                                            <div class="input-group">
                                                <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span></div>
                                                {{ Form::text('tid', gen4tid('RjR-', @$last_rjc->tid+1), ['class' => 'form-control round', 'disabled']) }}
                                                <input type="hidden" name="tid" value="{{ @$last_rjc->tid+1 }}">
                                            </div>
                                        </div>
                                        <div class="col-sm-4"><label for="report_date" class="caption">Report {{trans('general.date')}}</label>
                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span>
                                                </div>
                                                {{ Form::text('report_date', null, ['class' => 'form-control round datepicker']) }}
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
                                        <div class="col-sm-4"><label for="prepared_by" class="caption">Prepared By <span class="text-danger">*<span></label>
                                            <div class="input-group">
                                                <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span>
                                                </div>
                                                {{ Form::text('prepared_by', null, ['class' => 'form-control round', 'placeholder' => 'Prepared By']) }}
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <label for="technician" class="caption"> Technician <span class="text-danger">*<span></label>
                                            {{ Form::text('technician', null, ['class' => 'form-control round required', 'placeholder' => 'Technician', 'autocomplete'=>'false', 'id'=>'technician', 'required' => 'required']) }}
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
                                            <label for="subject" class="caption">Subject / Title <span class="text-danger">*<span></label>
                                            {{ Form::text('subject', null, ['class' => 'form-control round required', 'placeholder' => 'Subject / Title', 'autocomplete'=>'false', 'id'=>'subject']) }}
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
        date: {format: "{{config('core.user_date_format')}}", autoHide: true}
    };

    // initialize html editor
    editor();
    // ajax setup
    $.ajaxSetup(config.ajax);
    // initialize date picker 
    $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
    // select2
    $('#project').select2({
        allowClear: true,
        placeholder: 'Search by Project No, Project Title, Qt/PI No, Ticket No'
    }).val('').change();

    // on project select
    const projects = @json($projects);
    $('#project').change(function() {
        projects.forEach(prj => {
            if (prj.id == $(this).val()) {
                $('#subject').val(prj.name);
                prj.quotes.forEach(qt => {
                    if (qt.id == prj.main_quote_id) {
                        $('#client_ref').val(qt.client_ref).prop('readonly', true);
                    }
                });
            }
        });
    });

    // product row
    function productRow(i=0) {            
        return `
            <tr>
                <td><input type="text" class="form-control"  required="required" name="tag_number[]" placeholder="Search Equipment" id="tag_number-${i}"></td>
                <td><input type="text" class="form-control amnt" name="joc_card[]" id="joc_card-${i}"></td>
                <td><input type="text" class="form-control prc" name="equipment_type[]" id="equipment_type-${i}"></td>
                <td><input type="text" class="form-control r" name="make[]" id="make-${i}"></td>
                <td><input type="text" class="form-control" name="capacity[]" id="capacity-${i}"></td>
                <td><input type="text" class="form-control" name="location[]" id="location-${i}"></td>
                <td><input type="text" class="form-control datepicker" name="last_service_date[]" id="last_service_date-${i}"></td>
                <td><input type="text" class="form-control datepicker" name="next_service_date[]" id="next_service_date-${i}"></td>
                <td class="text-center">
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Action
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a class="dropdown-item removeProd" href="javascript:void(0);" data-rowid="${i}" >Remove</a>
                            <a class="dropdown-item up" href="javascript:void(0);">Up</a>
                            <a class="dropdown-item down" href="javascript:void(0);">Down</a>
                        </div>
                    </div>
                </td>
                <input type="hidden" name="row_index[]" value="0" id="rowindex-${i}">
            </tr>
        `;
    }

    // assign row index
    function assignIndex() {
        $('#equipment tr').each(function(i) {
            if (i == 0) return;
            const index = $(this).index();
            $(this).find('input[name="row_index[]"]').val(index);
        });
    }

    // on clicking add product
    let productIndx = 0;
    $('#addqproduct').click(function() {
        const i = productIndx++;
        const row = productRow(i);
        $('#equipment tr:last').after(row);   
        $('#tag_number-' + i).autocomplete(autocompleteProp(i));

        $('#joc_card-' + i).val($('#jobcard').val());
        $('#last_service_date-'+ i).datepicker(config.date).datepicker('setDate', new Date());
        $('#next_service_date-'+ i).datepicker(config.date).datepicker('setDate', new Date());
        assignIndex();
    });
    $('#addqproduct').click();

    // on clicking equipment drop down options
    $("#equipment").on("click", ".up,.down,.removeProd", function() {
        var row = $(this).parents("tr:first");
        if ($(this).is('.up')) row.insertBefore(row.prev());
        if ($(this).is('.down')) row.insertAfter(row.next());
        if ($(this).is('.removeProd')) $(this).closest('tr').remove();
        assignIndex();
    });

    // autocompleteProp returns autocomplete object properties
    function autocompleteProp(i) {
        return {
            source: function(request, response) {
                $.ajax({
                    url: baseurl + 'equipments/search/' + $("#client_id").val(),
                    dataType: "json",
                    method: 'post',
                    data: {
                        keyword: request.term, 
                    },
                    success: function(data) {
                        response($.map(data, function(v) {
                            const label = `${v.customer} ${v.name} ${v.make_type} ${v.capacity} ${v.location}`
                            const value = v.unique_id;
                            const data = v;
                            return {label, value, data};
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

                const lastDate = data.last_maintenance_date? new Date(data.last_maintenance_date) : '';
                const nextDate = data.next_maintenance_date? new Date(data.next_maintenance_date) : '';
                $('#last_service_date-'+i).datepicker('setDate', lastDate);
                $('#next_service_date-'+i).datepicker('setDate', nextDate);
            }
        };
    }    
    
    // check if file input has file and set caption validation to required
    const keys = ['one', 'two', 'three', 'four'];
    keys.forEach(v => {
        $('#image_'+v).on('change', function() {
            if ($(this).get(0).files.length > 0) $('#caption_'+v).prop('required', true);
            else $('#caption_'+v).prop('required', false);
        });
    });    
</script>
@endsection
