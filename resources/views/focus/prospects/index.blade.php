@extends ('core.layouts.app')

@section('title', 'Prospects Management')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">Prospects Management</h4>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right mr-3">
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
                        <div class="card-body">
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
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                <table id="prospects-table" class="table table-striped table-bordered zero-configuration"
                                    cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Names</th>
                                            <th>Industry</th>
                                            <th>Region</th>
                                            <th>Company</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Reminder Date</th>
                                            <th>Remark</th>
                                            <th>Follow up</th>
                                            <th>Status</th>
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

            init() {
                $.ajaxSetup(config.ajax);
                this.draw_data();
                this.showModal();
                //form remark
                remark: @json(@$remark),
                $('#reminder_date').datepicker(config.date).datepicker('setDate', new Date());
            },

            showModal(){
                $('#prospects-table tbody').on('click','#follow', function(e) {
                 var id = $('#follow').attr('data-id');  
                //show modal
                $('#remarksModal').modal('show');

                //set prospect id to form
                $('#prospect_id').val(id);

                $.ajax({
                    url: "{{ route('biller.prospects.followup') }}",
                    type: 'POST',
                    data: {
                        id: id
                    },
                    success: function(response) {

                        $('#tableModal').append(response);
                    }
                });

                $('#save_remark').on('click', function(e) {

                    var recepient = $('#recepient').val();
                    var reminder_date = $('#reminder_date').val();
                    var remarks = $('#remarks').val();

                    //disable button
                    $("#save_remark").prop("disabled", true);
                    let formData = $('#save_remark').parents('form').serializeArray();
                    $.ajax({
                        url: "remarks",
                        type: 'POST',
                        data: formData,
                        success: function(response) {
                            $('#remarks_table').remove();
                            $('#tableModal').append(response);
                        },
                        error: function(error) {
                            console.log(error.responseText);

                        }
                    });

                    $('#recepient').val('');
                    $('#reminder_date').val('');
                    $('#remarks').val('');
                    $("#save_remark").prop("disabled", false);
                });

                $('#remarksModal').on('hidden.bs.modal', function(e) {
                    $('#remarks_table').remove();
                    $('#prospect_id').val('');

                });
            });
            },
          

            draw_data() {
                $('#prospects-table').dataTable({
                    stateSave: true,
                    processing: true,
                    responsive: true,
                    language: {
                        @lang('datatable.strings')
                    },
                    ajax: {
                        url: '{{ route('biller.prospects.get') }}',
                        type: 'post',
                    },
                    columns: [{
                            data: 'DT_Row_Index',
                            name: 'id'
                        },
                        {
                            data: 'name',
                            name: 'name'
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
                            data: 'company',
                            name: 'company'
                        },


                        {
                            data: 'email',
                            name: 'email'
                        },
                        {
                            data: 'phone',
                            name: 'phone'
                        },
                        {
                            data: 'reminder_date',
                            name: 'reminder_date'
                        },
                        {
                            data: 'remarks',
                            name: 'remarks'
                        },
                        {
                            data: 'follow_up',
                            name: 'follow_up'
                        },

                        {
                            data: 'status',
                            name: 'status'
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
                        targets: [5]
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
