@extends('core.layouts.app')

@php
    $query_str = request()->getQueryString();
    $quote_label = trans('labels.backend.quotes.management');
    if ($query_str == 'page=pi') $quote_label = 'Proforma Invoice Management';
@endphp

@section ('title', $quote_label)

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">{{ $quote_label }}</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.quotes.partials.quotes-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="form-group row">
                        <div class="col-4">
                            <div class="d-inline">Filter Criteria (Quote / PI):</div>                             
                            <div class="d-inline">
                                @php
                                    $criteria = [
                                        'Unapproved', 'Approved & Unbudgeted', 'Budgeted & Unverified', 'Verified with LPO & Uninvoiced',
                                        'Verified without LPO & Uninvoiced', 'Approved without LPO & Uninvoiced',
                                    ];
                                @endphp
                                <select name="filter" class="custom-select" id="status_filter">
                                    <option value="">-- Choose Filter Criteria --</option>
                                    @foreach ($criteria as $val)
                                        <option value="{{ $val }}">{{ $val }}</option>
                                    @endforeach
                                </select>
                            </div>                                                     
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-2">
                            <input type="button" name="search" id="search" value="Filter" class="btn btn-info btn-sm" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-content">
                <div class="card-body">  
                    
                    <table id="quotes-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ $query_str == 'page=pi' ? '#PI' : '#Quote'  }} No</th>
                                <th>Customer & Branch</th>   
                                <th>Title</th>                                                                       
                                <th>{{ trans('general.amount') }} (Ksh.)</th>
                                <th>Client Ref</th>
                                <th>Ticket No</th>
                                <th>{{ trans('general.status') }}</th>
                                <th>Verified</th>   
                                <th>Date</th>                                       
                                <th>{{ trans('labels.general.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="8" class="text-center text-success font-large-1">
                                    <i class="fa fa-spinner spinner"></i></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>             
    </div>
</div>
@endsection

@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    // on clicking search
    $('#search').click(function() {
        $('#quotes-table').DataTable().destroy();
        return draw_data($('#status_filter').val());          
    });

    // Initialize datepicker
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    $('#start_date').datepicker('setDate', new Date());
    $('#end_date').datepicker('setDate', new Date());

    setTimeout(() => draw_data(), @json(config('master.delay')));

    function draw_data(status_filter='') {
        const language = {@lang('datatable.strings')};
        const table = $('#quotes-table').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            stateSave: true,
            language,
            ajax: {
                url: "{{ route('biller.quotes.get') }}",
                type: 'POST',
                data: {
                    status_filter,
                    page: location.href.includes('page=pi') ? 'pi' : 0
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
                    data: 'total',
                    name: 'total'
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
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'verified',
                    name: 'verified'
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
            order:[[0, 'desc']],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: {
                buttons: [
                    {
                        extend: 'csv',
                        footer: true,
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7]
                        }
                    },
                    {
                        extend: 'excel',
                        footer: true,
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7]
                        }
                    },
                    {
                        extend: 'print',
                        footer: true,
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7]
                        }
                    }
                ]
            }
        });
    }
</script>
@endsection