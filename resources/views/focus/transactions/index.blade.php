@extends ('core.layouts.app')

@section ('title', 'Transactions Management')

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

    <div class="card">
        <div class="card-body">
            @php
                $model_details = array(
                    'tr_category' => [
                        $words['name'] => $words['name_data'],
                        trans('general.description') => $segment['note'],
                        trans('transactions.debit') => amountFormat($segment->amount->sum('debit')),
                        trans('transactions.credit') => amountFormat($segment->amount->sum('credit')),
                    ],
                    'account' => [
                        $words['name'] => $words['name_data'],
                        trans('accounts.number') => $segment['number'],
                        trans('transactions.debit') => amountFormat($segment->amount->sum('debit')),
                        trans('transactions.credit') => amountFormat($segment->amount->sum('credit'))
                    ],
                    'customer' => [
                        $words['name'] => $words['name_data'],
                        trans('customers.email') => $segment['email'],
                        trans('transactions.debit') => amountFormat($segment->amount->sum('debit')),
                        trans('transactions.credit') => amountFormat($segment->amount->sum('credit'))
                    ],
                );

                $rows = array();
                if ($input['rel_type'] == 0) $rows = $model_details['tr_category']; 
                elseif ($input['rel_type'] < 9) $rows = $model_details['customer'];
                elseif ($input['rel_type'] == 9) $rows = $model_details['account'];
            @endphp
            <table id="modelsTbl" class="table table-md table-bordered zero-configuration" cellspacing="0" width="100%">
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
                                        <th>Transaction Type</th>
                                        <th>Supplier</th>                                      
                                        <th>Note</th>
                                        <th>{{ trans('transactions.debit') }}</th>
                                        <th>{{ trans('transactions.credit') }}</th>
                                        <th>Tr Date</th>
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
        const p_rel_id = @json(@$input['rel_id']);
        const p_rel_type = @json(@$input['rel_type']);
        const language = {
            @lang('datatable.strings')
        };
        const dataTable = $('#transactionsTbl').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            stateSave: true,
            language,
            ajax: {
                url: '{{ route("biller.transactions.get") }}',
                type: 'post',
                data: {p_rel_id, p_rel_type},
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
