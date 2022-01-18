@extends ('core.layouts.app')

@php
    $query_str = request()->getQueryString();
    $quote_label = trans('labels.backend.quotes.management');
    if ($query_str == 'page=pi') $quote_label = 'Proforma Invoice Management';
@endphp

@section ('title', $quote_label)

@section('content')
<div>
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-2">
                <h4 class="content-header-title">{{ $quote_label }}</h4>
            </div>
            <div class="content-header-right col-md-6 col-12">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        @include('focus.quotes.partials.quotes-header-buttons')
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
                                        <input type="text" name="start_date" id="start_date" class="date30 form-control form-control-sm datepicker" />
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text" name="end_date" id="end_date" class="form-control form-control-sm datepicker" />
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
                                            <th>Title</th>
                                            @if ($query_str == 'page=pi')
                                                <th>#PI</th>
                                                <th>{{ trans('customers.customer') }}</th>
                                                <th>PI Date</th>
                                            @else
                                                <th>#{{ trans('quotes.quote') }}</th>
                                                <th>{{ trans('customers.customer') }}</th>
                                                <th>{{ trans('quotes.invoicedate') }}</th>
                                            @endif                                            
                                            <th>{{ trans('general.amount') }} (Ksh.)</th>
                                            <th>{{ trans('general.status') }}</th>
                                            <th>Verified</th>
                                            <th>Client Ref</th>
                                            <th>Ticket No</th>
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
    const time = @json(config('master.delay'));
    setTimeout(() => draw_data(), time);

    // Update view button link td after dataTable has been drawn
    setTimeout(() => {
        const queryString = location.search;
        $('#quotes-table tbody tr').each(function() {
            const $a = $(this).find('td').eq(9).find('a').eq(2);
            const href = $a.attr('href');
            if (queryString.includes('page=pi')) {
                $a.attr('href', href + queryString);
            }
        });
    }, time+300);

    // on clicking search by date
    $('#search').click(function() {
        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();
        if (start_date && end_date) {
            $('#quotes-table').DataTable().destroy();
            return draw_data(start_date, end_date);
        } 
        alert("Date range is Required");            
    });

    // Initialize datepicker
    $('.datepicker').datepicker({ format: "{{ config('core.user_date_format') }}" })
    $('#start_date').datepicker('setDate', new Date());
    $('#end_date').datepicker('setDate', new Date());

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    function draw_data(start_date = '', end_date = '') {
        const segment = @json($segment);
        const input = @json($input);
        const tableLang = { @lang('datatable.strings') };

        const table = $('#quotes-table').dataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            stateSave: true,
            language: tableLang,
            ajax: {
                url: "{{ route('biller.quotes.get') }}",
                type: 'post',
                data: {
                    i_rel_id: segment['id'],
                    i_rel_type: input['rel_type'],
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
                    data: 'notes',
                    name: 'notes'
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
                    data: 'created_at',
                    name: 'created_at'
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
                    data: 'verified',
                    name: 'verified'
                },
                {
                    data: 'client_ref',
                    name: 'client_ref'
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