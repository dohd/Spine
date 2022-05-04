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
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    setTimeout(() => draw_data(), @json(config('master.delay')));

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
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    $('#start_date').datepicker('setDate', new Date());
    $('#end_date').datepicker('setDate', new Date());

    function draw_data(start_date = '', end_date = '') {
        const language = {@lang('datatable.strings')};
        const table = $('#quotes-table').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            stateSave: true,
            language,
            ajax: {
                url: "{{ route('biller.quotes.get') }}",
                type: 'post',
                data: {
                    start_date: start_date,
                    end_date: end_date,
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