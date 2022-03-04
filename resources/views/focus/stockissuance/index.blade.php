@extends ('core.layouts.app')

@section ('title', 'Stock Issuance Management')

@section('content')
<div>
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-2">
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
                                        <input type="text" name="start_date" id="start_date" data-toggle="datepicker" class="date30 form-control form-control-sm" autocomplete="off" />
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text" name="end_date" id="end_date" class="form-control form-control-sm" data-toggle="datepicker" autocomplete="off" />
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
@include('focus.stockissuance.modal.merged_log')
@endsection

@section('after-scripts')
{{-- For DataTables --}}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    $.ajaxSetup({headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }});

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

    function draw_data(start_date = '', end_date = '') {
        const segment = @json($segment);
        const input = @json($input);
        const table = $('#quotes-table').dataTable({
            ajax: {
                url: "{{ route('biller.stockissuance.get') }}",
                type: 'post',
                data: {
                    start_date, end_date,
                    i_rel_id: segment['id'],
                    i_rel_type: input['rel_type'],
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
                    data: 'quote_date',
                    name: 'quote_date'
                },
                {
                    data: 'project_number',
                    name: 'project_number'
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

    // assign quote_id to modal
    $('#quotes-table').on('click', '.issued-stock', function() {
        const quoteId = $(this).attr('data-id');
        $('#mergedLog').attr('data-id', quoteId);
    });
    // On Opening Modal
    $('#mergedLog').on('shown.bs.modal', function() {
        draw_data2();
    });
    function draw_data2(start_date = '', end_date = '') {
        const table = $('#mergedLogTbl').dataTable({
            destroy: true,
            ajax: {
                url: "{{ route('biller.stockissuance.getlog') }}",
                type: 'POST',
                data: {start_date, end_date},
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
                    data: 'unit',
                    name: 'unit'
                },
                {
                    data: 'issue_qty',
                    name: 'issue_qty'
                },
                {
                    data: 'reqxn',
                    name: 'reqxn'
                },
                {
                    data: 'warehouse',
                    name: 'warehouse'
                },
                {
                    data: 'date',
                    name: 'date'
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

    // On posting issued Stock
    $('#post-stock').click(function() {
        const quoteId = $('#mergedLog').attr('data-id');
        $.ajax({
            url: "{{ route('biller.stockissuance.post_issuedstock') }}",
            method: 'POST',
            dataType: 'json',
            data: {id: quoteId}
        });
        $('#mergedLog').modal('hide');

    });

    // On delete log
    $('#mergedLogTbl').on('click', '.delete-log', function() {
        const $row = $(this).parents('tr:first');
        const logId = $row.find('td:last').children('button').attr('data-id');

        // $.ajax({url: "{{ route('biller.stockissuance.delete_log') }}?id=" + logId })
        // .done(function(data) {
        //     $row.remove();
        // });
    });

    function tableConfig() {
        const tableLang = {@lang('datatable.strings')};
        return {
            processing: true,
            serverSide: true,
            responsive: true,
            stateSave: true,
            language: tableLang,
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