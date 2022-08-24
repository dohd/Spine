@extends ('core.layouts.app')

@section ('title', 'Invoice Payment Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Invoice Payment Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.invoices.partials.payments-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">                           
                            <table id="paymentTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>PMT No</th>
                                        <th>Customer</th>
                                        <th>Account</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Allocate</th>
                                        <th>Mode</th>
                                        <th>Reference</th>
                                        <th>Invoice</th>
                                        <th>Payment Type</th>
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
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }});
    setTimeout(() => draw_data(), "{{ config('master.delay') }}");

    // Initialize datepicker
    $('.datepicker')
    .datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    .datepicker('setDate', new Date())

    function draw_data() {
        const cols = ['tid', 'customer', 'account','date', 'amount', 'allocate_ttl', 'payment_mode', 'reference', 'invoice_tid', 'payment_type']
        .map(v => ({data: v, name: v}));
        const language = {@lang('datatable.strings')};
        var dataTable = $('#paymentTbl').dataTable({
            processing: true,
            stateSave: true,
            responsive: true,
            deferRender: true,
            language,
            ajax: {
                url: "{{ route('biller.invoices.get_payments') }}",
                type: 'POST',
            },
            columns: [{
                    data: 'DT_Row_Index',
                    name: 'id'
                },
                ...cols,
                {
                    data: 'actions',
                    name: 'actions',
                    searchable: false,
                    sortable: false
                }
            ],
            columnDefs: [
                { type: "custom-number-sort", targets: [4, 5] },
                { type: "custom-date-sort", targets: 3 }
            ],
            orderBy: [[0, "desc"]],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }
</script>
@endsection