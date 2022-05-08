@extends('core.layouts.app')

@section('title', 'Transactions Management')

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
            $debit = amountFormat($segment->debit_ttl);
            $credit = amountFormat($segment->credit_ttl);
            $model_details = array_map(function ($v) use($words, $debit, $credit) {
                $v = array_merge([$words['name'] => $words['name_data']], $v, [
                    'Debit' => $debit,
                    'Credit' => $credit
                ]);
                return $v;                    
            }, $model_details);

            $rows = array();
            if ($input['rel_type'] == 0) $rows = $model_details['tr_category']; 
            elseif ($input['rel_type'] < 9) $rows = $model_details['customer'];
            elseif ($input['rel_type'] == 9) $rows = $model_details['account'];
        @endphp
        <div class="card">
            <div class="card-body">
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
                            <table id="transactionsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>{{ trans('labels.backend.transactions.table.id') }}</th>  
                                        <th>Type</th>
                                        <th>Reference</th>                                      
                                        <th>Note</th>
                                        <th>{{ trans('transactions.debit') }}</th>
                                        <th>{{ trans('transactions.credit') }}</th>
                                        <th>Date</th>
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

    function draw_data() {
        const rel_id = @json(@$input['rel_id']);
        const rel_type = @json(@$input['rel_type']);
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
                data: {rel_id, rel_type},
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
                    data: 'supplier',
                    name: 'supplier'
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