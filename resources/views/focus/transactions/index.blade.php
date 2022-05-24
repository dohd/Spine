@extends('core.layouts.app')

@section('title', 'Transactions Management')

@if ($words)
    @php
        $model_details = [
            'tr_category' => [trans('general.description') => $segment->note],
            'customer' => [trans('customers.email') => $segment->email],
            'account' => [
                'Account No' => $segment->number, 
                'Account Type' => $segment->account_type, 
                'Note' => $segment->note
            ],
        ];
        $totals = [amountFormat($segment->debit), amountFormat($segment->credit)];
        $model_details = array_map(function ($v) use($words, $totals) {
            $v = array_merge([$words['name'] => $words['name_data']], $v, [
                'Debit' => $totals[0],
                'Credit' => $totals[1]
            ]);
            return $v;                    
        }, $model_details);

        $rows = array();
        if ($input['rel_type'] == 0) $rows = $model_details['tr_category']; 
        elseif ($input['rel_type'] < 9) $rows = $model_details['customer'];
        elseif ($input['rel_type'] == 9) $rows = $model_details['account'];
    @endphp
@endif

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Transactions Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.transactions.partials.transactions-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <!-- Account info -->
    @if ($words)
        <div class="card">
            <div class="card-body">
                <h5>Ledger Account</h5>
                <table class="table table-sm table-bordered">
                    <tbody>
                        @foreach ($rows as $key => $val)
                            <tr>
                                <th>{{ $key }}</th>
                                <td>{!! $val !!} </td>
                            </tr> 
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-2">{{ trans('general.search_date')}}</div>
                                <div class="col-2">
                                    <input type="text" name="start_date" id="start_date" class="form-control form-control-sm datepicker">
                                </div>
                                <div class="col-2">
                                    <input type="text" name="end_date" id="end_date" class="form-control form-control-sm datepicker">
                                </div>
                                <div class="col-2">
                                    <input type="button" name="search" id="search" value="Search" class="btn btn-info btn-sm">
                                </div>
                            </div>
                            <hr>                            
                            <table id="transactionsTbl" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>{{ trans('labels.backend.transactions.table.id') }}</th>  
                                        <th>Type</th>
                                        @if ($is_tax)
                                            <th>Cutomer PIN</th>   
                                        @else
                                            <th>Reference</th>                                      
                                        @endif
                                        <th>Note</th>
                                        @if ($is_tax)
                                            <th>VAT(%)</th>
                                            <th>VAT Amount</th>   
                                        @endif
                                        <th>{{ trans('transactions.debit') }}</th>
                                        <th>{{ trans('transactions.credit') }}</th>
                                        <th>Date</th>
                                        <th>Created At</th>
                                        <th>{{ trans('labels.general.actions') }}</th>
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
@endsection

@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    setTimeout(() => draw_data(), "{{ config('master.delay') }}");
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });    

    // datepicker
    $('.datepicker')
    .datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    .datepicker('setDate', new Date());

    // datefilter
    $('#search').click(() => {
        const start_date = $('#start_date').val();
        const end_date = $('#end_date').val();
        $('#transactionsTbl').DataTable().destroy();
        draw_data(start_date, end_date);
    });

    function draw_data(start_date='', end_date='') {
        const system = "{{ request('system') }}"
        const obj = [];
        if (system == 'tax') {
            obj.push({data: 'vat_rate', name: 'vat_rate'});
            obj.push({data: 'vat_amount', name: 'vat_amount'});
        }
        const input = @json(@$input);
        const language = {@lang('datatable.strings')};
        const dataTable = $('#transactionsTbl').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            stateSave: true,
            language,
            ajax: {
                url: '{{ route("biller.transactions.get") }}',
                type: 'post',
                data: {...input, system, start_date, end_date},
            },
            columns: [{
                    data: 'DT_Row_Index',
                    name: 'id'
                },
                {
                    data: 'tr_type',
                    name: 'tr_type'
                },
                {
                    data: 'reference',
                    name: 'reference'
                },
                {
                    data: 'note',
                    name: 'note'
                },
                ...obj,
                {
                    data: 'debit',
                    name: 'debit'
                },
                {
                    data: 'credit',
                    name: 'credit'
                },
                {
                    data: 'tr_date',
                    name: 'tr_date'
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'actions',
                    name: 'actions',
                    searchable: false,
                    sortable: false
                }
            ],
            order: [
                [0, "desc"]
            ],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: {
                buttons: [
                    {
                        extend: 'csv',
                        footer: true,
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6]
                        }
                    },
                    {
                        extend: 'excel',
                        footer: true,
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6]
                        }
                    },
                    {
                        extend: 'print',
                        footer: true,
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6]
                        }
                    }
                ]
            }
        });
    }
</script>
@endsection