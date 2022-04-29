@extends ('core.layouts.app')

@section ('title', 'Issuance Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Issuance Management</h4>
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
                                        <th>Project No</th> 
                                        <th># {{ trans('quotes.quote') }} / PI No</th>
                                        <th>Title</th>
                                        <th>Customer</th>                                               
                                        <th>Status</th>                                                                
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
        const language = { @lang("datatable.strings") };
        const table = $('#quoteTbl').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            stateSave: true,
            language,
            ajax: {
                url: "{{ route('biller.issuance.get') }}",
                type: 'post',
                data: {start_date, end_date},
            },
            columns: [{
                    data: 'DT_Row_Index',
                    name: 'id'
                },
                {
                    data: 'project_tid',
                    name: 'project_tid'
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
                    data: 'customer',
                    name: 'customer'
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