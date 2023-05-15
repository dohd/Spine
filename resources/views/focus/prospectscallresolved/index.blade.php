@extends ('core.layouts.app')

@section('title', 'Prospects Management')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">Prospects Call Resolved Management</h4>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-auto float-right mr-3">
                    <div class="media-body media-right text-right">
                        @include('focus.prospects.partials.prospects-header-buttons')
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        {{-- <div class="card-body">
                            <div class="row no-gutters">
                                <div class="col-sm-3 col-md-2 h4">Open Prospects</div>
                                <div class="col-sm-2 col-md-1 h4 text-primary font-weight-bold">{{ $open_prospect }}</div>
                                <div class="col-sm-12 col-md-1 h4 text-primary font-weight-bold">
                                    {{ numberFormat(div_num($open_prospect, $total_prospect) * 100) }}%</div>
                            </div>
                            <div class="row no-gutters">
                                <div class="col-sm-3 col-md-2 h4">Closed Prospects</div>
                                <div class="col-sm-2 col-md-1 h4 text-success font-weight-bold">{{ $closed_prospect }}</div>
                                <div class="col-sm-12 col-md-1 h4 text-success font-weight-bold">
                                    {{ numberFormat(div_num($closed_prospect, $total_prospect) * 100) }}%</div>
                            </div>
                        </div> --}}
                        {{-- <div class="row mb-3 ml-1">
                            <div class="col-2">
                                <label for="client">Title</label>                             
                                <select name="bytitle" class="custom-select" id="bytitle" data-placeholder="Choose Title">
                                    <option value="">Choose Title</option>
                                    @foreach ($titles as $title)
                                        <option value="{{ $title->title }}">{{ $title->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-2">
                                <label for="client">Call Status</label>                             
                                <select name="bycallstatus" class="custom-select" id="bycallstatus" data-placeholder="Choose Call Status">
                                    <option value="">Choose Call Status</option>
                                    <option value="called">Called</option>
                                    <option value="calledrescheduled">Called But Rescheduled</option>
                                    <option value="callednotpicked">Called Not Picked</option>
                                    <option value="callednotavailable">Called Not Available</option>
                                    <option value="notcalled">Not Called</option>
                                    
                                </select>
                            </div>
                            
                            <div class="col-2">
                                <label for="client">Temperate Status</label>                             
                                <select name="bytemperate" class="custom-select" id="bytemperate" data-placeholder="Choose Temperate Status">
                                    <option value="">Choose Temperate Status</option>
                                    <option value="hot">Hot</option>
                                    <option value="warm">Warm</option>
                                    <option value="cold">Cold</option>
                                   
                                </select>
                            </div>
                            <div class="col-2">
                                <label for="client">Prospect Status</label>                             
                                <select name="bystatus" class="custom-select" id="bystatus" data-placeholder="Choose Prospect Status">
                                    <option value="">Choose Prospect Status</option>
                                    <option value="open">Open</option>
                                    <option value="won">Closed-Won</option>
                                    <option value="lost">Closed-Lost</option>
                                   
                                   
                                </select>
                            </div> --}}

                    </div>

                </div>
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <table id="prospectscallresolved-table"
                                class="table table-striped table-bordered zero-configuration" cellspacing="0"
                                width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Title</th>
                                        <th>Company Name</th>
                                        <th>Industry</th>
                                        {{-- <th>Contact Name</th>
                                        <th>Phone</th> --}}
                                        <th>Region</th>
                                        <th>Type</th>
                                        <th>Reminder Date</th>
                                        <th>Follow up</th>
                                       
                                        <th>CallStatus</th>
                                        <th>Status</th>
                                        <th>Reason</th>
                                        <th>{{ trans('labels.general.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="100%" class="text-center text-success font-large-1">
                                            <i class="fa fa-spinner spinner"></i>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    @include('focus.prospects.partials.remarks_modal')
    @include('focus.prospects.partials.call_modal')
@endsection

@section('after-scripts')
    {{ Html::script(mix('js/dataTable.js')) }}
    <script>
        const config = {
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }
            },
            date: {
                format: "{{ config('core.user_date_format') }}",
                autoHide: true
            },
        };

        const Index = {
            title: @json(request('bytitle')),
            temperate: @json(request('bytemperate')),
            callstatus: @json(request('bycallstatus')),
            status: @json(request('bystatus')),
            init() {
                $.ajaxSetup(config.ajax);
                this.draw_data();
                this.showModal();
                this.showCallModal();
                //form remark
                remark: @json(@$remark),

                //filters
                $('#bytitle').change(this.titleChange);
                $('#bytemperate').change(this.temperateChange);
                $('#bycallstatus').change(this.callStatusChange);
                $('#bystatus').change(this.statusChange);

                //callModal
                $('#callModal').find('.erp-status').change(this.erpChange);
                $('#callModal').find('.challenges-status').change(this.challengesChange);
                $('#callModal').find('.demo-status').change(this.demoChange);
                $('#callModal').find('.call-status').change(this.callTypeChange);
                this.dismissCallModal();
            },
            titleChange() {
                Index.title = $(this).val();
                $('#prospects-table').DataTable().destroy();
                return Index.draw_data();
            },
            temperateChange() {
                Index.temperate = $(this).val();
                $('#prospects-table').DataTable().destroy();
                return Index.draw_data();
            },
            callStatusChange() {
                Index.callstatus = $(this).val();
                $('#prospects-table').DataTable().destroy();
                return Index.draw_data();
            },
            statusChange() {
                Index.status = $(this).val();
                $('#prospectscallresolved-table').DataTable().destroy();
                return Index.draw_data();
            },
            showModal() {
                $('#prospectscallresolved-table tbody').on('click', '#follow', function(e) {
                    var id = $(this).attr('data-id');

                    //show modal
                    $('#remarksModal').modal('show');


                    //varible to check if data is saved
                    let saved = false;
                    //set prospect id to form
                    $('#prospect_id').val(id);
                    
                     //append response to call history
                     $.ajax({
                        url: "{{ route('biller.prospects.followup') }}",
                        type: 'post',
                        data: {
                            id: id
                        },
                        success: function(response) {
                            $('#tableModal').append(response);
                        }
                    });
                     //append prospect details
                     $.ajax({
                        url: "{{ route('biller.prospects.fetchprospect') }}",
                        type: 'post',
                        data: {
                            id: id
                        },
                        success: function(response) {
                           
                            $('#prospectTableDetailsRemarks').append(response);
                        }
                    });
                     //append prospectcall resolved details
                     $.ajax({
                        url: "{{ route('biller.prospectcallresolves.fetchprospectrecord') }}",
                        type: 'post',
                        data: {
                            id: id
                        },
                        success: function(response) {
                            $('#recordsTableModal').append(response);
                        }
                    });
                    $('#save_remark').on('click', function(e) {

                        var recepient = $('#remarksrecepient').val();
                        var reminder_date = $('#remarksreminder_date').val();
                        var remarks = $('#remarksanyremarks').val();

                        //disable button
                        $("#save_remark").prop("disabled", true);
                        let formData = $('#save_remark').parents('form').serializeArray();
                        
                        $.ajax({
                            url: "remarks",
                            type: 'POST',
                            data: formData,
                            success: function(response) {
                                saved = true;
                                $('#remarks_table').remove();
                                $('#recordsTableModal').append(response);
                            },
                            error: function(error) {
                                console.log(error.responseText);

                            }
                        });

                        $('#remarksrecepient').val('');
                        $('#remarksreminder_date').val('');
                        $('#remarksanyremarks').val('');
                        $("#save_remark").prop("disabled", false);
                    });

                    $('#remarksModal').on('hidden.bs.modal', function(e) {
                        $('#remarks_table').remove();
                        $('#prospect_id').val();
                        $('#prospect_prospect_table').remove();
                        $('#records_table').remove();
                        id = "";
                        //saved ? window.location.reload() : null;
                    });
                });
            },

            showCallModal() {
                $('#prospectscallresolved-table tbody').on('click', '#call', function(e) {
                    var id = $(this).attr('data-id');
                    var call_id = $(this).attr('call-id');
                    //show modal
                    $('#callModal').modal('show');

                  
                    //picked
                    $('#picked_prospect_id').val(id);
                    
                    //notpicked
                    
                    $('#notpicked_prospect_id').val(id);

                    //pickedbusy
                    
                    $('#busyprospect_id').val(id);

                    //notavailable
                    $('#notavailable_prospect').val(id);
                    //append response to call history
                    $.ajax({
                        url: "{{ route('biller.prospects.followup') }}",
                        type: 'post',
                        data: {
                            id: id
                        },
                        success: function(response) {
                            $('#remarksTableModal').append(response);
                        }
                    });
                    //append prospect details
                    $.ajax({
                        url: "{{ route('biller.prospects.fetchprospect') }}",
                        type: 'post',
                        data: {
                            id: id
                        },
                        success: function(response) {
                            $('#prospectTableDetails').append(response);
                        }
                    });

                    
                });
            },
            erpChange() {
                if ($(this).val() == 0) {
                    $("#erp_div").css("display", "none");
                } else {
                    $("#erp_div").css("display", "block");
                }
            },
            challengesChange() {
                if ($(this).val() == "0") {
                    $("#erpchallenges").css("display", "none");
                } else {
                    $("#erpchallenges").css("display", "block");
                }
            },
            demoChange() {
                if ($(this).val() == "0") {
                    $("#demo").css("display", "none");
                    $("#notes").val('');
                    $("#demo_date").val('');
                } else {
                    $("#demo").css("display", "");
                }
            },
            callTypeChange() {

                if ($(this).val() == 'picked') {
                    $("#div_picked").css("display", "block");
                    $("#div_notpicked").css("display", "none");
                    $("#div_picked_busy").css("display", "none");
                    $("#div_notpicked_available").css("display", "none");
                } else if ($(this).val() == 'pickedbusy') {
                    $("#div_picked_busy").css("display", "block");
                    $("#div_picked").css("display", "none");
                    $("#div_notpicked").css("display", "none");
                    $("#div_notpicked_available").css("display", "none");
                } else if ($(this).val() == 'notpicked') {
                    $("#div_notpicked").css("display", "block");
                    $("#div_picked").css("display", "none");
                    $("#div_picked_busy").css("display", "none");
                    $("#div_notpicked_available").css("display", "none");
                } else if ($(this).val() == 'notavailable') {
                    $("#div_notpicked_available").css("display", "block");
                    $("#div_notpicked").css("display", "none");
                    $("#div_picked").css("display", "none");
                    $("#div_picked_busy").css("display", "none");

                }


            },
            dismissCallModal() {


                $('#callModal').on('hidden.bs.modal', function() {
                    $("#notes").val('');
                    $("#current_erp_challenges").val('');
                    $('#picked_prospect_id').val('');
                    $('#notpicked_prospect_id').val('');
                    $('#busyprospect_id').val('');
                    $('#notavailable_prospect').val('');
                    $("#save_call_chat").attr("disabled", false);
                    $("#save_reshedule").attr("disabled", false);
                    $("#save_reminder").attr("disabled", false);
                    $("#notavailable").attr("disabled", false);
                    $('#remarks_table').remove();
                    $('#prospect_prospect_table').remove();
                    id= "";
                    //saved?window.location.reload():null;
                });
            },


            draw_data() {

                $('#prospectscallresolved-table').dataTable({
                    stateSave: true,
                    processing: true,
                    responsive: true,
                    language: {
                        @lang('datatable.strings')
                    },
                    ajax: {
                        url: '{{ route('biller.prospectcallresolves.get') }}',
                        type: 'post',
                        // data: {
                        //     bytitle: this.title,
                        //     bytemperate: this.temperate,
                        //     bycallstatus: this.callstatus,
                        //     bystatus: this.status,
                        // }
                    },
                    columns: [{
                            data: 'DT_Row_Index',
                            name: 'id'
                        },
                        {
                            data: 'title',
                            name: 'title'
                        },
                        {
                            data: 'company',
                            name: 'company'
                        },

                        {
                            data: 'industry',
                            name: 'industry'
                        },
                        // {
                        //     data: 'name',
                        //     name: 'name'
                        // },
                        // {
                        //     data: 'phone',
                        //     name: 'phone'
                        // },

                        {
                            data: 'region',
                            name: 'region'
                        },
                        {
                            data: 'temperate',
                            name: 'temperate'
                        },
                        {
                            data: 'reminder_date',
                            name: 'reminder_date'
                        },
                        {
                            data: 'follow_up',
                            name: 'follow_up'
                        },
                        {
                            data: 'call_status',
                            name: 'call_status'
                        },
                        {
                            data: 'status',
                            name: 'status'
                        },
                        {
                            data: 'reason',
                            name: 'reason'
                        },

                        {
                            data: 'actions',
                            name: 'actions',
                            searchable: false,
                            sortable: false
                        }
                    ],
                    columnDefs: [{
                        type: "custom-date-sort",
                        targets: []
                    }],
                    order: [
                        [0, "desc"]
                    ],
                    searchDelay: 500,
                    dom: 'Blfrtip',
                    buttons: ['csv', 'excel', 'print'],
                });
            }
        };
        $(() => Index.init());
    </script>


@endsection
