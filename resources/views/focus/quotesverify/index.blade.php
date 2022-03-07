@extends ('core.layouts.app')

@section ('title', 'Verification Management')

@section('content')
<div>
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-2">
                <h4 class="content-header-title">Verification Management</h4>
            </div>   
            <div class="content-header-right col-md-6 col-12">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        <div class="btn-group">
                            <a href="{{ route('biller.rjcs.index') }}" class="btn btn-success">
                                <i class="fa fa-list-alt"></i> Rjc
                            </a>                         
                        </div>
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
                                    <div class="col-md-2">{{ trans('general.search_date')}} </div>
                                    <div class="col-md-2">
                                        <input type="text" name="start_date" id="start_date" class="form-control datepicker date30  form-control-sm" autocomplete="off" />
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text" name="end_date" id="end_date" class="form-control datepicker form-control-sm" autocomplete="off" />
                                    </div>
                                    <div class="col-md-2">
                                        <input type="button" name="search" id="search" value="Search" class="btn btn-info btn-sm" />
                                    </div>
                                </div>
                                <hr>
                                <table id="quotes-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ trans('customers.customer') }}</th>
                                            <th># {{ trans('quotes.quote') }} / PI</th>
                                            <th>Title</th>                                            
                                            <th>{{ trans('general.amount') }} (Ksh.)</th>
                                            <th>Quote / PI Date</th>
                                            <th>Project No</th>
                                            <th>LPO No</th>
                                            <th>Ticket No</th>
                                            <th>Client Ref</th>
                                            <th>Verified</th>
                                            <th>{{ trans('labels.general.actions') }}</th>
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
@endsection

@section('after-scripts')
{{-- For DataTables --}}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }
    });
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

    $('.datepicker')
    .datepicker({ format: "{{ config('core.user_date_format') }}" })
    .datepicker('setDate', new Date())
    .change(function() { $(this).datepicker('hide') });


    function draw_data(start_date = '', end_date = '') {
        const tableLang = { @lang('datatable.strings') };
        const table = $('#quotes-table').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            stateSave: true,
            language: tableLang,
            ajax: {
                url: '{{ route("biller.quotes.get_project") }}',
                type: 'post',
                data: {
                    start_date: start_date,
                    end_date: end_date,
                    pi_page: location.href.includes('page=pi') ? 1 : 0
                },
            },
            columns: [{
                    data: 'DT_Row_Index',
                    name: 'id'
                },
                {
                    data: 'customer',
                    name: 'customer'
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
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'tid',
                    name: 'tid'
                },
                {
                    data: 'lpo_number',
                    name: 'lpo_number'
                },
                {
                    data: 'lead_tid',
                    name: 'lead_tid'
                },
                {
                    data: 'client_ref',
                    name: 'client_ref'
                },
                {
                    data: 'verified',
                    name: 'verified'
                },
                {
                    data: 'actions',
                    name: 'actions',
                    searchable: false,
                    sortable: false
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
</script>
@endsection