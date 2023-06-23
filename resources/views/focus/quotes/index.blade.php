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
        <div class="card" id="filters">
            <div class="card-content">
                <div class="card-body">
                    <div class="form-group row">
                        <div class="col-4">
                            <label for="client">Customer</label>                             
                            <select name="client_id" class="custom-select" id="client" data-placeholder="Choose Client">
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->company }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-4">
                            <label for="filter">Filter Criteria</label>                             
                            @php
                                $criteria = [
                                    'Unapproved', 'Approved without LPO & Uninvoiced', 'Approved & Unbudgeted',  
                                    'Budgeted & Unverified', 'Verified with LPO & Uninvoiced', 'Verified without LPO & Uninvoiced',
                                    'Approved & Uninvoiced', 
                                    'Invoiced', 'Invoiced & Due', 'Invoiced & Partially Paid', 'Invoiced & Paid',
                                    'Cancelled'
                                ];
                            @endphp
                            <select name="filter" class="custom-select" id="status_filter">
                                <option value="">-- Choose Filter Criteria --</option>
                                @foreach ($criteria as $val)
                                    <option value="{{ $val }}">{{ $val }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-3">
                            <label for="total">Total Amount</label>                             
                            <input type="text" name="amount_total" class="form-control" id="amount_total" readonly>
                        </div>
                    </div>                    
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-content">
                <div class="card-body">  
                    <table id="quotesTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>   
                                <th>{{ $query_str == 'page=pi' ? '#PI' : '#Quote'  }} No</th>
                                <th>Customer - Branch</th>   
                                <th>Title</th>                                                                       
                                <th>Amount</th>
                                <th>Approval Date</th>
                                <th>Client Ref</th>                                
                                <th>Ticket No</th>
                                <th>Invoice No</th>
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
@endsection

@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    const config = {
        ajaxSetup: {headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }},
        datepicker: {format: "{{ config('core.user_date_format') }}", autoHide: true}
    };

    const Index = {
        customers: @json($customers),

        init(config) {
            $.ajaxSetup(config.ajaxSetup);
            $('.datepicker').datepicker(config.datepicker).datepicker('setDate', new Date());
            $('#client').select2({allowClear: true}).val('').trigger('change');

            $('#filters').on('change', '#status_filter, #client', this.filterCriteriaChange);
            this.drawDataTable();
        },

        filterCriteriaChange() {
            $('#quotesTbl').DataTable().destroy();
            return Index.drawDataTable({
                status_filter: $('#status_filter').val(),
                client_id: $('#client').val()
            });   
        },

        drawDataTable(params={}) {
            $('#quotesTbl').dataTable({
                processing: true,
                responsive: true,
                stateSave: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: "{{ route('biller.quotes.get') }}",
                    type: 'POST',
                    data: {
                        ...params,
                        page: location.href.includes('page=pi') ? 'pi' : 'qt'
                    },
                    dataSrc: ({data}) => {
                        $('#amount_total').val('');
                        if (data.length) $('#amount_total').val(data[0].sum_total);                            
                        return data;
                    },
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    ...[
                        'date', 'tid', 'customer', 'notes', 'total', 'approved_date', 
                        'client_ref', 'lead_tid', 'invoice_tid'
                    ].map(v => ({data: v, name: v})),
                    {data: 'actions', name: 'actions', searchable: false, sortable: false}
                ],
                columnDefs: [
                    { type: "custom-number-sort", targets: 5 },
                    { type: "custom-date-sort", targets: [1,6] }
                ],
                order:[[0, 'desc']],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        }
    };

    $(() => Index.init(config));
</script>
@endsection