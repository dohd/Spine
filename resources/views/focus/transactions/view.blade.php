@extends ('core.layouts.app')

@section ('title', 'Transactions Management')

@php
    // transaction links to parent resources
    $tr = $transaction;
    $tr_types = [
        'pmt' => 'PAYMENT',
        'bill' => 'BILL', 
        'inv' => 'INVOICE', 
        'loan' => 'LOAN', 
        'chrg' => 'CHARGE',
        'stock' => 'STOCK',
        'withholding' => 'WITHHOLDING',
        'cnote' => 'CREDIT NOTE',
        'genjr' => 'JOURNAL ENTRY'
    ];

    $void = 'javascript:';

    $bill_url = $void;
    if ($tr->bill) {
        $id = $tr->bill->po_id? $tr->bill->po_id : $tr->bill->id;
        if (isset($tr->bill->po_id)) $bill_url = route('biller.purchaseorders.show', $id);
        elseif (isset($tr->bill->id)) $bill_url = route('biller.purchases.show', $id);
    }

    $tr_type_urls = [
        'BILL' => $bill_url,
        'PAYMENT' => isset($tr->paidinvoice->customer) ? route('biller.invoices.show_payment', $tr->paidinvoice) : $void,
        'INVOICE' => $tr->invoice ? route('biller.invoices.show', $tr->invoice) : $void,
        'LOAN' => $tr->loan ? route('biller.loans.show', $tr->loan) : $void,
        'CHARGE' => $tr->charge ? route('biller.charges.show', $tr->charge) : $void,
        'STOCK' => $tr->issuance ? route('biller.issuance.show', $tr->issuance) : $void,
        'WITHHOLDING' => $tr->withholding ? route('biller.withholdings.show', $tr->withholding) : $void,
        'JOURNAL ENTRY' => $tr->journalentry ? route('biller.journals.show', $tr->journalentry) : $void,
        'CREDIT NOTE' => $tr->creditnote ? route('biller.creditnotes.edit', $tr->creditnote) : $void,
    ];
@endphp

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h3 class="content-header-title">Transactions Management</h3>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.transactions.partials.transactions-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-content">
            <div class="card-header">
                <a href="javascript:" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editTrModal">
                    <i class="fa fa-pencil"></i> Edit
                </a>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-sm">
                    @php
                        $tr_detail_type = isset($tr_types[$tr->tr_type]) ? $tr_types[$tr->tr_type] : '';
                        $tr_details = [
                            'Account' => $tr->account->holder,
                            'Category' => $tr->category->name,
                            'Type' => $tr_detail_type,
                            'Debit' => amountFormat($tr['debit']),
                            'Credit' => amountFormat($tr['credit']),
                            'Date' => dateFormat($tr['tr_date']),
                            trans('general.employee') => $tr->user->first_name . ' ' . $tr->user->last_name,
                            trans('general.note') => $tr->note,                                    
                        ];
                    @endphp
                    @foreach ($tr_details as $key => $value)
                        <tr>
                            <th>{{ $key }}</th>
                            <td>
                                @if ($key == 'Type' && $tr_detail_type)                                                
                                    <a href="{{ $tr_type_urls[$value] }}">{{ $value }}</a>
                                @else
                                    {{ $value }} &nbsp;&nbsp;
                                    @if ($key == 'Category')
                                        <a href="{{ route('biller.print_payslip', [$transaction['id'], 1, 1]) }}" class="btn btn-blue round">
                                            <span class="fa fa-print" aria-hidden="true"></span>
                                        </a>
                                    @endif
                                @endif 
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-content">
            <div class="card-body">
                <table id="transactionsTbl" class="table table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>{{ trans('labels.backend.transactions.table.id') }}</th>  
                            <th>Type</th>
                            <th>Reference</th>                                      
                            <th>Note</th>
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
@include('focus.transactions.partials.edit-modal')
@endsection

@section("after-scripts")
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script type="text/javascript">
    $.ajaxSetup({headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }});

    const transaction = @json($transaction);
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})    
    if (transaction.tr_date) $('#tr_date').datepicker('setDate', new Date(transaction.tr_date));

    $('#editTrModal').on('change', '#credit, #debit', function() {
        $(this).val(accounting.formatNumber($(this).val()));
        if ($(this).is('#debit')) {
            $('#debit').attr('disabled', false);
            $('#credit').val('0.00').attr('disabled', true);
        } else {
            $('#credit').attr('disabled', false);
            $('#debit').val('0.00').attr('disabled', true);
        }
    });

    // on account search
    $('#account').select2({
        dropdownParent: $('#editTrModal'),
        ajax: {
            url: "{{ route('biller.transactions.account_search') }}",
            dataType: 'json',
            quietMillis: 50,
            data: ({term}) => ({keyword: term}),
            processResults: data => {
                const results = data.map(v => ({id: v.id, text: v.holder}));
                return {results}; 
            },  
        }
    });

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
            data: {tr_tid: "{{ $tr->tid }}", tr_id: "{{ $tr->id }}"},
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
</script>
@endsection