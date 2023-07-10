@extends('core.layouts.app')

@section('title', 'Transactions Management')

@if ($words)
    @php
        $model_details = [
            'tr_category' => [trans('general.description') => $segment->note],
            'customer' => [trans('customers.email') => $segment->email],
            'account' => [
                'Account No' => $segment->number, 
                $words['name'] => $words['name_data'],
                'Account Type' => $segment->account_type, 
                'Note' => $segment->note,
                'Debit' => amountFormat($segment->debit),
                'Credit' => amountFormat($segment->credit),
                'Ledger Balance' => in_array($segment->account_type, ['Asset', 'Expense'])? 
                    amountFormat($segment->debit - $segment->credit) : amountFormat($segment->credit - $segment->debit),
            ],
        ];        

        $rows = [];
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

    <!-- Account Summary -->
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
    <!-- End Account Summary -->

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            @if ($words)
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label for="">Debit</label>
                                                <input type="text" class="form-control form-control-sm tbl_debit" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="">Credit</label>
                                                <input type="text" class="form-control form-control-sm tbl_credit" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="">Balance</label>
                                                <input type="text" class="form-control form-control-sm tbl_balance" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="row no-gutters">
                                @php
                                    $now = date('d-m-Y');
                                    $start = date('d-m-Y', strtotime("{$now} - 3 months"));
                                @endphp
                                <div class="col-md-2">{{ trans('general.search_date')}}:</div>
                                <div class="col-md-1 mr-1">
                                    <input type="text" name="start_date" value="{{ $start }}" id="start_date" class="form-control form-control-sm datepicker">
                                </div>
                                <div class="col-md-1 mr-1">
                                    <input type="text" name="end_date" value="{{ $now }}" id="end_date" class="form-control form-control-sm datepicker">
                                </div>
                                <div class="col-md-1">
                                    <input type="button" name="search" id="search" value="Search" class="btn btn-info btn-sm">
                                </div>
                            </div>
                            <hr> 

                            <table id="transactionsTbl" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th> 
                                        <th>Tr No.</th>
                                        <th>Type</th>
                                        <th>{{ $is_tax? 'Customer PIN' : 'Ledger Account' }}</th>
                                        <th>Note</th>
                                        @if ($is_tax)
                                            <th>VAT %</th>
                                            <th>VAT Amount</th>   
                                        @endif
                                        <th>{{ trans('transactions.debit') }}</th>
                                        <th>{{ trans('transactions.credit') }}</th>
                                        <th>Balance</th>
                                        <th class="th-date">Date</th>
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
    const config = {
        ajax: {
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        },
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
    };

    const Index = {        
        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date);

            this.drawDataTable();
            $('#search').click(this.dateSearchClick);
        },

        dateSearchClick() {
            $('#transactionsTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        drawDataTable() {
            let obj = [];
            const system = @json(request('system'));
            if (system == 'tax') obj = ['vat_rate', 'vat_amount'];
            const input = @json(@$input);

            $('#transactionsTbl').dataTable({
                processing: true,
                responsive: true,
                stateSave: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: '{{ route("biller.transactions.get") }}',
                    type: 'post',
                    data: {system, start_date: $('#start_date').val(), end_date: $('#end_date').val(), ...input},
                    dataSrc: ({data}) => {
                        $('.tbl_debit').val('');
                        $('.tbl_credit').val('');
                        $('.tbl_balance').val('');
                        if (data.length && data[0].aggregate) {
                            const aggr = data[0].aggregate;
                            $('.tbl_debit').val(aggr.debit);
                            $('.tbl_credit').val(aggr.credit);
                            $('.tbl_balance').val(aggr.balance);
                        }
                        return data;
                    },
                },
                columns: [{
                        data: 'DT_Row_Index',
                        name: 'id'
                    },
                    ...[
                        'tid', 'tr_type', 'reference', 'note', ...obj, 'debit', 'credit',
                        'balance', 'tr_date'
                    ].map(v => ({data: v, name: v})),
                    {
                        data: 'actions',
                        name: 'actions',
                        searchable: false,
                        sortable: false
                    }
                ],
                columnDefs: [
                    { type: "custom-number-sort", targets: [5,6,7] },
                    { type: "custom-date-sort", targets: 8 }
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print']
            });

        },
    };

    $(() => Index.init());
</script>
@endsection