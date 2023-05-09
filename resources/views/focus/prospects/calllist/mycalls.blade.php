@extends ('core.layouts.app')

@section('title', 'My Today Calls')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">My Today Calls</h4>
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
                        <div class="card-body">
                            <input type="hidden" name="list_id" id="list_id" value="">
                            <div class="col-4">
                                <label for="client">All CallLists</label>                             
                                <select name="calllist_id" class="custom-select" id="calllist_id" data-placeholder="Choose CallList">
                                    <option value="0">Choose Call List</option>
                                    @foreach ($calllists as $calllist)
                                        
                                        <option value="{{ $calllist->id }}">{{ $calllist->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- <div class="row no-gutters">
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
                            </div> --}}
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                <table id="mytodaycalllist-table"
                                    class="table table-striped table-bordered zero-configuration" cellspacing="0"
                                    width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Title</th>
                                            <th>Company/Name</th>
                                            <th>Industry</th>
                                            <th>Call</th>
                                            {{-- <th>Email</th> --}}
                                            <th>Phone</th>
                                            <th>Region</th>
                                            <th>Call Status</th>
                                            <th>Call Date</th>

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
            callListId: @json(request('id')),
            init() {
                $.ajaxSetup(config.ajax);
                this.draw_data();
                this.showModal();
                this.dismissModal();
                
                $('#callModal').find('.erp-status').change(this.erpChange);
                $('#callModal').find('.challenges-status').change(this.challengesChange);
                $('#callModal').find('.demo-status').change(this.demoChange);
                $('#callModal').find('.call-status').change(this.callTypeChange);
                // $('#demo_date').datepicker(config.date).datepicker('setDate', new Date());
               
                $('#calllist_id').change(this.callListChange);
                
            },  

            showModal() {
                $('#mytodaycalllist-table tbody').on('click', '#call', function(e) {
                    var id = $(this).attr('data-id');
                    var call_id = $(this).attr('call-id');
                    //show modal
                    $('#callModal').modal('show');



                    $('#prospect_id').val(id);
                    $('#hidden_prospect').val(id);
                    $('#busyprospect_id').val(id);
                    $('#call_id').val(call_id);
                    $('#busycall_id').val(call_id);

                });
            },
          
            callListChange(){
            Index.callListId = $(this).val();
            $('#mytodaycalllist-table').DataTable().destroy();
            return Index.draw_data();
            },
            erpChange(){
                if ($(this).val() == 0) {
                    $("#erp_div").css("display", "none");
                } else {
                    $("#erp_div").css("display", "block");
                }
            },
            challengesChange(){
                if ($(this).val() == "0") {
                    $("#erpchallenges").css("display", "none");
                } else {
                    $("#erpchallenges").css("display", "block");
                }
            },
            demoChange(){
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
                }
                else if($(this).val() == 'pickedbusy'){
                    $("#div_picked_busy").css("display", "block");
                    $("#div_picked").css("display", "none");
                    $("#div_notpicked").css("display", "none");
                }
                 else if($(this).val() == 'notpicked') {
                    $("#div_notpicked").css("display", "block");
                    $("#div_picked").css("display", "none");
                    $("#div_picked_busy").css("display", "none");
                }


            },

            dismissModal() {

                $('#callModal').on('hidden.bs.modal', function() {
                    $("#notes").val('');
                    $("#demo_date").datepicker(config.date).datepicker('setDate', new Date());
             
                    $("#current_erp_challenges").val('');
                    $('#prospect_id').val('');
                    $('#hidden_prospect').val('');
                    $('#busyprospect_id').val('');
                    $('#busycall_id').val('');
                    $('#call_id').val('');
                   
                    
                });
            },



            draw_data() {
                console.log(this.callListId);
                $('#mytodaycalllist-table').dataTable({
                    stateSave: true,
                    processing: true,
                    responsive: true,
                    language: {
                        @lang('datatable.strings')
                    },
                    ajax: {
                        url: '{{ route('biller.calllists.fetchtodaycalls') }}',
                        type: 'post',
                        data: {
                            id: this.callListId
                        }
                       
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
                        {
                            data: 'call_prospect',
                            name: 'call_prospect'
                        },
                        {
                            data: 'phone',
                            name: 'phone'
                        },

                        {
                            data: 'region',
                            name: 'region'
                        },
                        {
                            data: 'call_status',
                            name: 'call_status'
                        },
                        {
                            data: 'call_date',
                            name: 'call_date'
                        },

                    ],
                    columnDefs: [{
                        type: "custom-date-sort",
                        targets: [8]
                    }],
                    order: [
                        [0, "desc"]
                    ],
                    searchDelay: 500,
                    dom: 'Blfrtip',
                    buttons: ['csv', 'excel', 'print'],
                });
            },


        };
        $(() => Index.init());
    </script>


@endsection
