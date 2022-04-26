@extends ('core.layouts.app')

@section ('title', 'Stock Issuance Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Stock Issuance Management</h4>
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
                                    <input type="text" name="start_date" id="start_date" class="date30 form-control form-control-sm datepicker">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="end_date" id="end_date" class="form-control form-control-sm datepicker">
                                </div>
                                <div class="col-md-2">
                                    <input type="button" name="search" id="search" value="Search" class="btn btn-info btn-sm">
                                </div>
                            </div>
                            <hr>
                            <table id="quoteTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ trans('customers.customer') }}</th>
                                        <th># {{ trans('quotes.quote') }} / PI</th>
                                        <th>Title</th>                                            
                                        <th>{{ trans('general.amount') }} (Ksh.)</th>
                                        <th>Quote / PI Date</th>        
                                        <th>Project No</th>                                   
                                        <th>{{ trans('labels.general.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="8" class="text-center text-success font-large-1">
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
@endsection

@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    setTimeout(() => draw_data(), "{{ config('master.delay') }}");
    $.ajaxSetup({headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }});

    $('#search').click(function() {
        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();
        if (start_date && end_date) {
            $('#quoteTbl').DataTable().destroy();
            return draw_data(start_date, end_date);
        } 
        alert("Date range is Required");            
    });

    $('.datepicker')
        .datepicker({ format: "{{ config('core.user_date_format') }}", autoHide: true})
        .datepicker('setDate', new Date());

    function draw_data(start_date = '', end_date = '') {
        const table = $('#quoteTbl').dataTable({
            ajax: {
                url: "{{ route('biller.stockissuance.get') }}",
                type: 'post',
                data: {start_date, end_date},
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
                    data: 'quote_date',
                    name: 'quote_date'
                },
                {
                    data: 'tid',
                    name: 'tid'
                },
                {
                    data: 'actions',
                    name: 'actions',
                    searchable: false,
                    sortable: false
                }
            ],
            ...tableConfig()
        });
    }

    function tableConfig() {
        const language = {@lang('datatable.strings')};
        return {
            processing: true,
            serverSide: true,
            responsive: true,
            stateSave: true,
            language,
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
        }
    }
</script>
@endsection