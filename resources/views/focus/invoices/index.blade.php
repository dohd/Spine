@extends ('core.layouts.app')

@section ('title', 'Project Invoice Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title mb-0">Project Invoice Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.invoices.partials.invoices-header-buttons')
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
                                    <input type="text" name="start_date" id="start_date" class="date30 form-control form-control-sm datepicker">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="end_date" id="end_date" class="form-control form-control-sm datepicker">
                                </div>
                                <div class="col-md-2">
                                    <input type="button" name="search" id="search" value="Search" class="btn btn-info btn-sm" />
                                </div>
                            </div>
                            <hr>
                            <table id="invoices-table_{{ $input['meta'] }}" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>#Invoice No</th>
                                        <th>{{ trans('customers.customer') }}</th>
                                        <th>Subject</th>
                                        <th>{{ trans('invoices.invoice_date') }}</th>
                                        <th>{{ trans('general.amount') }}</th>
                                        <th>{{ trans('general.status') }}</th>
                                        <th>Due Date</th>
                                        <th>#Quote / PI No</th>
                                        <th>#Ticket No</th>
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

@endsection

@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    setTimeout(() => draw_data(), "{{ config('master.delay') }}");

    const meta = @json($input['meta']);
    $('#invoices-table_'+ meta +'_wrapper').removeClass('form-inline');
    
    $('#search').click(function() {
        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();
        if (start_date && end_date) {
            $('#invoices-table_'+ meta).DataTable().destroy();
            return draw_data(start_date, end_date);
        } 
        alert("Date range is Required");
    });

    // Initialize datepicker
    $('.datepicker')
        .datepicker({ format: "{{ config('core.user_date_format') }}"})
        .datepicker('setDate', new Date());

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }});

    function draw_data(start_date = '', end_date = '') {
        const segmentId = @json($segment);
        const relType = @json($input);
        const subJson = @json($input)['sub_json'];
        const tableLan = { @lang('datatable.strings') };

        var dataTable = $('#invoices-table_' + meta).dataTable({
            processing: true,
            stateSave: true,
            serverSide: true,
            responsive: true,
            deferRender: true,
            language: tableLan,
            ajax: {
                url: "{{ route('biller.invoices.get') }}",
                type: 'post',
                data: {
                    i_rel_id: segmentId['id'],
                    i_rel_type: relType['rel_type'],
                    subJson: { subJson },
                    start_date: start_date,
                    end_date: end_date
                },
            },
            columns: [{
                    data: 'DT_Row_Index',
                    name: 'id'
                },
                {
                    data: 'tid',
                    name: 'tid'
                },
                {
                    data: 'customer',
                    name: 'customer'
                },
                {
                    data: 'notes',
                    name: 'notes'
                },
                {
                    data: 'invoicedate',
                    name: 'invoicedate'
                },
                {
                    data: 'total',
                    name: 'total'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'invoiceduedate',
                    name: 'invoiceduedate'
                },
                {
                    data: 'quote_tid',
                    name: 'quote_tid'
                },
                {
                    data: 'lead_tid',
                    name: 'lead_tid'
                },
                {
                    data: 'actions',
                    name: 'actions',
                    searchable: false,
                    sortable: false
                }
            ],
            orderBy: [[0, "desc"]],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: {
                buttons: [{
                        extend: 'csv',
                        footer: true,
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5]
                        }
                    },
                    {
                        extend: 'excel',
                        footer: true,
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5]
                        }
                    },
                    {
                        extend: 'print',
                        footer: true,
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5]
                        }
                    }
                ]
            }
        });
    }
</script>
@endsection