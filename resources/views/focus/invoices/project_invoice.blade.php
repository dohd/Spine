@extends ('core.layouts.app')

@section ('title', 'Create Project Invoice')

@section('page-header')
    <h1>Create Project Invoice</h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">Create Project Invoice</h4>
                 
                </div>
                <div class="content-header-right col-md-6 col-12">

                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.customers.partials.customers-header-buttons')
                        </div>
                    </div>
                </div>
            </div>

          

            <div class="content-body">

                <div class="row">
                    <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">

                                <div class="col-sm-3">

                                    <div class="form-group">
                                        <button type="button" class="btn btn-xs btn-success update_product_location mt-2" data-type="add">Invoice Selected</button>
                                  


                                  
                                    </div>
                                </div>

                            <div class="col-sm-3">
                            <div class="form-group">
                                
                                <label><strong>Customer :</strong></label>
                                {!! Form::select('customer_id', $customers,  null, ['placeholder' => 'All Customers', 'id'=>'customer_id','class' => 'form-control']); !!}


                              
                            </div>
                        </div>

                        <div class="col-sm-3">
                            
                            <div class="form-group">
                                <label><strong>LPO :</strong></label>

                                {!! Form::select('lpo_number', $lpos,  null, ['placeholder' => 'All LPOS', 'id'=>'lpo_number','class' => 'form-control']); !!}
                           
                            </div>

                        </div>

                      

                        <div class="col-sm-3">
                            
                            <div class="form-group">
                                <label><strong>Project :</strong></label>

                                {!! Form::select('project_id', $projects,  null, ['placeholder' => 'All Projects', 'id'=>'project_id','class' => 'form-control']); !!}
                            
                            </div>
                        </div>
                            </div>
                        </div>
                    </div>
                </div>

                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <div class="card-content">

                                <div class="card-body">
                                    <table id="quotes-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th><input type="checkbox" id="select-all-row"></th>
                                                <th>{{ trans('customers.customer') }}</th>
                                                <th>Quote / PI Date</th>
                                                <th>LPO No</th>
                                               
                                                
                                                <th># {{ trans('quotes.quote') }} / PI</th>
                                                <th>Title</th>                                            
                                                <th>{{ trans('general.amount') }} (Ksh.)</th>
                                                <th>Verified (Ksh.)</th>
                                                
                                                
                                                <th>Project No</th>
                                              
                                                <th>Verified</th>
                                              
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="8" class="text-center text-success font-large-1"><i class="fa fa-spinner spinner"></i></td>
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
    @if(@$segment->group_data)
        @php
            $customergroup= $segment->group_data;
        @endphp
        @include("focus.modal.group_email_model")
    @endif
    @include("focus.customers.modal.selected_email")
    @include("focus.customers.modal.selected_sms")
    @include("focus.customers.modal.selected_delete")
@endsection


@section('after-scripts')
    {{-- For DataTables --}}
    {{ Html::script(mix('js/dataTable.js')) }}
    <script>
        $(function() {
            setTimeout(() => draw_data(), "{{ config('master.delay') }}");
    
            $('#search').click(function() {
                var start_date = $('#start_date').val();
                var end_date = $('#end_date').val();
                if (start_date && end_date) {
                    $('#quotes-table').DataTable().destroy();
                    return draw_data(start_date, end_date);
                } 
                alert("Date range is Required");            
            });
    
            $('[data-toggle="datepicker"]')
                .datepicker({ format: "{{ config('core.user_date_format') }}" })
                .datepicker('setDate', new Date());
        });
    
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });
    
        function draw_data(start_date = '', end_date = '',customer_id='', lpo_number='',project_id='') {
            const segment = @json($segment);
            const input = @json($input);
            const tableLang = { @lang('datatable.strings') };
    
            const table = $('#quotes-table').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                stateSave: true,
                language: tableLang,
                ajax: {
                    url: '{{ route("biller.quotes.get_univoiced_quote") }}',
                    type: 'post',
                    data: {
                        i_rel_id: segment['id'],
                        i_rel_type: input['rel_type'],
                        start_date: start_date,
                        end_date: end_date,
                        customer_id: customer_id,
                        lpo_number:lpo_number,
                        project_id:project_id,
                        pi_page: location.href.includes('page=pi') ? 1 : 0
                    },
                },
                columns: [

                    { data: 'mass_select' ,
                    searchable: false, sortable: false 
                    },
                    {
                        data: 'customer',
                        name: 'customer'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'lpo_number',
                        name: 'lpo_number'
                    },
                  
                  
                    {
                        data: 'tid',
                        name: 'tid'
                    },
                    {
                        data: 'notes',
                        name: 'notes'
                    },
                    {
                        data: 'total',
                        name: 'total'
                    },
                    {
                        data: 'verified_total',
                        name: 'verified_total'
                    },

                 
                    {
                        data: 'project_number',
                        name: 'project_number'
                    },
                   
                    {
                        data: 'verified',
                        name: 'verified'
                    }
                  
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: {
                    buttons: [
                        {
                            extend: 'csv',
                            footer: true,
                            exportOptions: {
                                columns: [0, 1]
                            }
                        },
                        {
                            extend: 'excel',
                            footer: true,
                            exportOptions: {
                                columns: [0, 1]
                            }
                        },
                        {
                            extend: 'print',
                            footer: true,
                            exportOptions: {
                                columns: [0, 1]
                            }
                        }
                    ]
                }
            });
        }

        $('#customer_id, #lpo_number, #project_id').change(function(){

            var customer_id=$('#customer_id').val()
            var lpo_number=$('#lpo_number').val()
            var project_id=$('#project_id').val()
            console.log(project_id);
           // console.log(lpo_number);
            
            var start_date='';
            var end_date='';
            $('#quotes-table').DataTable().destroy();
            return draw_data(start_date, end_date, customer_id, lpo_number,project_id);
     
    });

    $(document).on('click', '#select-all-row', function(e) {
    if (this.checked) {
        $(this)
            .closest('table')
            .find('tbody')
            .find('input.row-select')
            .each(function() {
                if (!this.checked) {
                    $(this)
                        .prop('checked', true)
                        .change();
                }
            });
    } else {
        $(this)
            .closest('table')
            .find('tbody')
            .find('input.row-select')
            .each(function() {
                if (this.checked) {
                    $(this)
                        .prop('checked', false)
                        .change();
                }
            });
    }
});
    </script>
@endsection
