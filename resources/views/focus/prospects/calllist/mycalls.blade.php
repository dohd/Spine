@extends ('core.layouts.app')

@section('title', 'My Today Calls')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">My Today Calls</h4>
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
                    {{-- <div class="card">
                        <div class="card-body">
                            <div class="row no-gutters">
                                <div class="col-sm-3 col-md-2 h4">Called</div>
                                <div class="col-sm-2 col-md-1 h4 text-primary font-weight-bold">{{ $called }}</div>
                                <div class="col-sm-12 col-md-1 h4 text-primary font-weight-bold">
                                    {{ numberFormat(div_num($called, $total_prospect) * 100) }}%</div>
                            </div>
                            <div class="row no-gutters">
                                <div class="col-sm-3 col-md-2 h4">Not Called</div>
                                <div class="col-sm-2 col-md-1 h4 text-success font-weight-bold">{{ $not_called }}</div>
                                <div class="col-sm-12 col-md-1 h4 text-success font-weight-bold">
                                    {{ numberFormat(div_num($not_called, $total_prospect) * 100) }}%</div>
                            </div>
                        </div>
                    </div> --}}
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                <table id="mytodaycalllist-table"
                                    class="table table-striped table-bordered zero-configuration" cellspacing="0"
                                    width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Names</th>
                                            
                                            <th>Call Date</th>
                                            <th>Call</th>

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

            init() {
                $.ajaxSetup(config.ajax);
                this.draw_data();
                this.showModal();
                this.dismissModal();
                $('#callModal').find('.call-status').change(this.callTypeChange);
                $('#reminder_date').datepicker(config.date).datepicker('setDate', new Date());
            },

            showModal() {
                $('#mytodaycalllist-table tbody').on('click', '#call', function(e) {
                    var id = $(this).attr('data-id');

                    //show modal
                    $('#callModal').modal('show');



                    $('#prospect_id').val(id);

                });
            },


            callTypeChange() {
                if ($(this).val() == 'picked') {
                    $("#div_picked").css("display", "block");
                    $("#div_notpicked").css("display", "none");
                } else {
                    $("#div_picked").css("display", "none");
                    $("#div_notpicked").css("display", "block");
                }


            },

            dismissModal() {
                $('#callModal').on('hidden.bs.modal', function() {

                });
            },



            draw_data() {
                $('#mytodaycalllist-table').dataTable({
                    stateSave: true,
                    processing: true,
                    responsive: true,
                    language: {
                        @lang('datatable.strings')
                    },
                    ajax: {
                        url: '{{ route('biller.calllists.mytodaycalls') }}',
                        type: 'post',
                    },
                    columns: [{
                            data: 'DT_Row_Index',
                            name: 'id'
                        },
                        {
                            data: 'prospect_id',
                            name: 'prospect_id'
                        },
                       
                        {
                            data: 'call_date',
                            name: 'call_date'
                        },
                        {
                            data: 'call_prospect',
                            name: 'call_prospect'
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
                        targets: [2]
                    }],
                    order: [
                        [0, "desc"]
                    ],
                    searchDelay: 500,
                    dom: 'Blfrtip',
                    buttons: ['csv', 'excel', 'print'],
                });
            },
            // draw_data() {
            //     $('#mytodaycalllist-table').dataTable({
            //         stateSave: true,
            //         processing: true,
            //         responsive: true,
            //         language: {
            //             @lang('datatable.strings')
            //         },
            //         ajax: {
            //             url: '{{ route('biller.calllists.mytoday') }}',
            //             type: 'post',
            //         },
            //         columns: [{
            //                 data: 'DT_Row_Index',
            //                 name: 'id'
            //             },
            //             {
            //                 data: 'name',
            //                 name: 'name'
            //             },
            //             {
            //                 data: 'email',
            //                 name: 'email'
            //             },
            //             {
            //                 data: 'phone',
            //                 name: 'phone'
            //             },
            //             {
            //                 data: 'company',
            //                 name: 'company'
            //             },
            //             {
            //                 data: 'industry',
            //                 name: 'industry'
            //             },
            //             {
            //                 data: 'region',
            //                 name: 'region'
            //             },
            //             {
            //                 data: 'call_status',
            //                 name: 'call_status'
            //             },
            //             {
            //                 data: 'call_prospect',
            //                 name: 'call_prospect'
            //             },
            //             {
            //                 data: 'actions',
            //                 name: 'actions',
            //                 searchable: false,
            //                 sortable: false
            //             }
            //         ],
            //         columnDefs: [{
            //             // type: "custom-date-sort",
            //             // targets: [5]
            //         }],
            //         order: [
            //             [0, "desc"]
            //         ],
            //         searchDelay: 500,
            //         dom: 'Blfrtip',
            //         buttons: ['csv', 'excel', 'print'],
            //     });
            // }

        };
        $(() => Index.init());
    </script>


@endsection
