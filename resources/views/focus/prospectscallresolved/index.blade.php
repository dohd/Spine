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
                        <div class="card-content">
                            <div class="card-body">

                                <div class="row">
                                    <div class="col-2">{{ trans('general.search_date') }} </div>
                                    <div class="col-2">
                                        <input type="text" name="start_date" id="start_date"
                                            class="form-control datepicker date30  form-control-sm" autocomplete="off" />
                                    </div>
                                    <div class="col-2">
                                        <input type="text" name="end_date" id="end_date"
                                            class="form-control datepicker form-control-sm" autocomplete="off" />
                                    </div>
                                    <div class="col-2">
                                        <input type="button" name="search" id="search" value="Search"
                                            class="btn btn-info btn-sm" />
                                    </div>
                                </div>

                                <hr>
                                <table id="prospectscallresolved-table"
                                    class="table table-striped table-bordered zero-configuration" cellspacing="0"
                                    width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Title</th>
                                            <th>Company Name</th>
                                            <th>Contact Person</th>
                                            <th>Phone</th>
                                            <th>Industry</th>
                                            <th>Region</th>
                                            <th>Type</th>
                                            <th>Reminder Date</th>
                                            <th>Follow up</th>
                                            <th>CallStatus</th>
                                            <th>Status</th>
                                            <th>Reason</th>
                                            {{-- <th>{{ trans('labels.general.actions') }}</th> --}}
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
            datepicker: {format: "{{ config('core.user_date_format') }}", autoHide: true}
        };

        const Index = {
            // title: @json(request('bytitle')),
            // temperate: @json(request('bytemperate')),
            // callstatus: @json(request('bycallstatus')),
            // status: @json(request('bystatus')),
            init() {
                $.ajaxSetup(config.ajax);
                $('.datepicker').datepicker(config.datepicker).datepicker('setDate', new Date());
                this.draw_data();
                this.showModal();
                this.showCallModal();
                //form remark
                remark: @json(@$remark),

                    //filters
                    // $('#bytitle').change(this.titleChange);
                    // $('#bytemperate').change(this.temperateChange);
                    // $('#bycallstatus').change(this.callStatusChange);
                    // $('#bystatus').change(this.statusChange);

                    //callModal
                $('#callModal').find('.erp-status').change(this.erpChange);
                $('#callModal').find('.challenges-status').change(this.challengesChange);
                $('#callModal').find('.demo-status').change(this.demoChange);
                $('#callModal').find('.call-status').change(this.callTypeChange);
                this.dismissCallModal();
                $('#search').click(this.searchDateClick);
            },
            
            // titleChange() {
            //     Index.title = $(this).val();
            //     $('#prospects-table').DataTable().destroy();
            //     return Index.draw_data();
            // },
            // temperateChange() {
            //     Index.temperate = $(this).val();
            //     $('#prospects-table').DataTable().destroy();
            //     return Index.draw_data();
            // },
            // callStatusChange() {
            //     Index.callstatus = $(this).val();
            //     $('#prospects-table').DataTable().destroy();
            //     return Index.draw_data();
            // },
            // statusChange() {
            //     Index.status = $(this).val();
            //     $('#prospectscallresolved-table').DataTable().destroy();
            //     return Index.draw_data();
            // },
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
                    id = "";
                    //saved?window.location.reload():null;
                });
            },

            searchDateClick() {
                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();
                if (!startDate || !endDate) return alert("Date range required!");
               
                $('#prospectscallresolved-table').DataTable().destroy();
                return Index.draw_data({
                    start_date: startDate,
                    end_date: endDate,
                });
            },

            draw_data(params={}) {

                $('#prospectscallresolved-table').dataTable({
                    stateSave: true,
                    processing: true,
                    responsive: true,
                    language: {
                        @lang('datatable.strings')
                    },
                    ajax: {
                        url: '{{ route('biller.prospectcallresolves.get') }}',
                        data: {
                        ...params,
                        pi_page: location.href.includes('page=pi') ? 1 : 0
                    },
                        type: 'post',
                    },
                    columns: [{
                            data: 'DT_Row_Index',
                            name: 'id'
                        },
                        {
                            data: 'date',
                            name: 'date'
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
                            data: 'contact_person',
                            name: 'contact_person'
                        },
                        {
                            data: 'phone',
                            name: 'phone'
                        },

                        {
                            data: 'industry',
                            name: 'industry'
                        },

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

                        // {
                        //     data: 'actions',
                        //     name: 'actions',
                        //     searchable: false,
                        //     sortable: false
                        // }
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
