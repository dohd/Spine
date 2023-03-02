@extends ('core.layouts.app')

@section ('title', 'Project Invoice Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Project Invoice Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.invoices.partials.invoices-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row form-group">
                            <div class="col-4">
                                <label for="customer">Customer</label>
                                <select name="customer_id" id="customer" class="form-control" data-placeholder="Choose Customer">
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->company }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-2">
                                <label for="invoice_status">Invoice Status</label>
                                <select name="invoice_status" id="inv_status" class="custom-select">
                                    <option value="">-- select status --</option>
                                    @foreach (['not yet due', 'due'] as $status)
                                        <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-2">
                                <label for="payment_status">Payment Status</label>
                                <select name="payment_status" id="pmt_status" class="custom-select">
                                    <option value="">-- select status --</option>
                                    @foreach (['unpaid', 'partially paid', 'paid'] as $status)
                                        <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>                            
                        </div>
                        <div class="row">
                            <div class="col-2">
                                <label for="amount">Total Amount</label>
                                <input type="text" id="amount_total" class="form-control" readonly>
                            </div>                            
                            <div class="col-2">
                                <label for="unallocate">Outstanding</label>
                                <input type="text" id="balance_total" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2">{{ trans('general.search_date')}} </div>
                                <div class="col-md-2">
                                    <input type="text" name="start_date" id="start_date" class="date30 form-control form-control-sm datepicker">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="end_date" id="end_date" class="form-control form-control-sm datepicker">
                                </div>
                                <div class="col-md-2">
                                    <input type="button" name="search" id="search" value="Search" class="btn btn-info btn-sm" />
                                </div>
                            </div>
                            <hr>
                            <table id="invoiceTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Customer</th>
                                        <th>#Inv No</th>                                        
                                        <th>Subject</th>
                                        <th>Inv Date</th>
                                        <th>Due Date</th>
                                        <th>{{ trans('general.amount') }}</th>
                                        <th>Outstanding</th>                                       
                                        <th>#Quote / PI No</th>
                                        <th>Last PMT Date</th>
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
{{ Html::script('focus/js/select2.min.js') }}
<script>
    const config = {
        ajax: { headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }},
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true}
    };

    const Index = {
        startDate: '',
        endDate: '',
        customerId: '',
        invoiceStatus: '',
        paymentStatus: '',

        init() {
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            this.drawDataTable();

            $('#inv_status').change(this.invoiceStatusChange);
            $('#pmt_status').change(this.paymentStatusChange);
            $('#customer').select2({allowClear: true}).val('').trigger('change')
            .change(this.customerChange);

            $('#search').click(this.searchClick);
        },

        searchClick() {
            Index.startDate = $('#start_date').val();
            Index.endDate =  $('#end_date').val();
            if (!Index.startDate || !Index.endDate ) 
                return alert("Date range is Required");

            $('#invoiceTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        invoiceStatusChange() {
            const lastOpt = $('#pmt_status option:eq(-1)');
            if ($(this).val() == 'due') {
                lastOpt.addClass('d-none');
            } else lastOpt.removeClass('d-none');
                
            Index.invoiceStatus = $(this).val();
            $('#invoiceTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        paymentStatusChange() {
            const lastOpt = $('#inv_status option:eq(-1)');
            if ($(this).val() == 'paid') {
                lastOpt.addClass('d-none');
            } else lastOpt.removeClass('d-none');

            Index.paymentStatus = $(this).val();
            $('#invoiceTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        customerChange() {
            Index.customerId = $(this).val();
            $('#invoiceTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        drawDataTable() {
            $('#invoiceTbl').dataTable({
                processing: true,
                stateSave: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: "{{ route('biller.invoices.get') }}",
                    type: 'post',
                    data: {
                        start_date: this.startDate, 
                        end_date: this.endDate, 
                        customer_id: this.customerId,
                        invoice_status: this.invoiceStatus,
                        payment_status: this.paymentStatus
                    },
                    dataSrc: ({data}) => {
                        $('#amount_total').val('');
                        $('#balance_total').val('');
                        if (data.length) {
                            const aggregate = data[0].aggregate;
                            $('#amount_total').val(aggregate.amount_total);
                            $('#balance_total').val(aggregate.balance_total);
                        }
                        return data;
                    },
                },
                columns: [{
                        data: 'DT_Row_Index',
                        name: 'id'
                    },
                    ...[
                        'customer', 'tid', 'notes', 'invoicedate', 'invoiceduedate', 'total', 'balance', 'quote_tid', 'last_pmt',
                    ].map(v => ({data: v, name: v})),
                    {
                        data: 'actions',
                        name: 'actions',
                        searchable: false,
                        sortable: false
                    }
                ],
                columnDefs: [
                    { type: "custom-number-sort", targets: [6, 7] },
                    { type: "custom-date-sort", targets: [4, 5, 9] }
                ],
                orderBy: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        },
    };
    
    $(() => Index.init());
</script>
@endsection