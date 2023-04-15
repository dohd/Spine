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
                                            <th>Company</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Reminder Date</th>
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
        setTimeout(() => draw_data(), "{{ config('master.delay') }}");
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        });

        $('#prospects-table tbody').on('click', '.follow', function(e) {
            //set datepicker so as to show calender on reminder_date
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

            const Form = {
                remark: @json(@$remark),


                init() {
                    $('#reminder_date').datepicker(config.date).datepicker('setDate', new Date());

                },



            };

            $(() => Form.init());



            var id = e.target.getAttribute('data-id');

            $.ajax({
                type: "post",
                url: "{{ route('biller.prospects.followup') }}",
                data: {
                    id: id,
                },

                success: function(response) {
                    if (!response.remark) {
                        swal({
                            title: 'Remarks is Not Found',
                            icon: "warning",
                            buttons: true,
                            dangerMode: true,
                            showCancelButton: true,
                        }, () => {
                            return;
                        });
                        return;
                    }
                    $('#remarksModal').modal('show');
                    $('#remarksModal').find('#remarks_table tbody').empty();
                    //append data fetched to table
                    $.each(response.remark, function(key, value) {
                        var row = $('<tr>');
                        var id = $('<td>').text(key + 1);
                        var cdate = $('<td>').text(value.created_at);
                        var recepient = $('<td>').text(value.recepient);
                        var remarks = $('<td>').text(value.remarks);
                        var reminderdate = $('<td>').text(value.reminder_date);

                        row.append(id);
                        row.append(cdate);
                        row.append(recepient);
                        row.append(remarks);
                        row.append(reminderdate);
                        $('#remarksModal').find('#remarks_table tbody').append(row);


                    });
                    //set prospect id to form
                    $('#prospect_id').val(id);

                    //on form submit(Creating Remark)
                    $('#remarkform').submit( function(e) {
                        e.preventDefault();
                       
                        var formData = $(this).serialize();
                        // console.log({{ route('biller.remarks.store') }});
                        // $.ajax({
                        //     url:'{{ route('biller.remarks.store') }}',
                        //     type:'POST',
                        //     data:formData,
                        //     success:function(response){
                        //         alert(response);
                        //     }
                        //     error:function(error){
                        //         alert(error);
                        //     }
                        // });
                    });
                }
            });
        });

        function draw_data() {
            const dataTable = $('#prospects-table').dataTable({
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
    </script>


@endsection
