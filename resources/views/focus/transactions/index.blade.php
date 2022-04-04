@extends ('core.layouts.app')

@section ('title', trans('labels.backend.transactions.management'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h4 class="mb-0">{{ trans('labels.backend.transactions.management') }}</h4>
        </div>
        <div class="content-header-right col-md-6 col-12">
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
            @endphp
            @if ($input['rel_type'] == 0)
                @foreach ($model_details['tr_category'] as $key => $value)
                    <div class="row">
                        <div class="col-sm-2">
                            <p>{{ $key }}</p>
                        </div>
                        <div class="col-sm-6">
                            <p>{{ $value }}</p>
                        </div>
                    </div>
                @endforeach
            @endif
            @if ($input['rel_type'] == 9)
                @foreach ($model_details['account'] as $key => $value)
                    <div class="row">
                        <div class="col-sm-2">
                            <p>{{ $key }}</p>
                        </div>
                        <div class="col-sm-6">
                            <p>{{ $value }}</p>
                        </div>
                    </div>
                @endforeach
            @endif
            @if ($input['rel_type'] && $input['rel_type'] < 9) 
                @foreach ($model_details['customer'] as $key => $value)
                    <div class="row">
                        <div class="col-sm-2">
                            <p>{{ $key }}</p>
                        </div>
                        <div class="col-sm-6">
                            <p>
                                @if ($value == $words['url'])
                                    {!! $value !!}
                                @else
                                    {{ $value }}
                                @endif
                            </p>
                        </div>
                    </div>
                @endforeach                
            @endif
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <table id="transactions-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>{{ trans('labels.backend.transactions.table.id') }}</th>                                        
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
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });    
    setTimeout(() => draw_data(), "{{ config('master.delay') }}");

    function draw_data() {
        const tableLan = {
            @lang('datatable.strings')
        };
        const p_rel_id = @json(@$input['rel_id']);
        const p_rel_type = @json(@$input['rel_type']);

        var dataTable = $('#transactions-table').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            stateSave: true,
            language: tableLan,
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
                [0, "asc"]
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
