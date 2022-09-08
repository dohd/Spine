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
                        <div class="row">
                            <div class="col-4">
                                <label for="customer">Customer</label>
                                <select name="customer_id" id="customer" class="form-control" data-placeholder="Choose Customer">
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->company }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-2">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="">-- select status --</option>
                                    @foreach (['not yet due', 'due', 'partially paid', 'fully paid'] as $status)
                                        <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-2">
                                <label for="amount">Total Amount (Ksh.)</label>
                                <input type="text" id="amount_total" class="form-control" readonly>
                            </div>                            
                            <div class="col-2">
                                <label for="unallocate">Outstanding (Ksh.)</label>
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
                                        <th>#Invoice No</th>                                        
                                        <th>Subject</th>
                                        <th>Date</th>
                                        <th>Due Date</th>
                                        <th>{{ trans('general.amount') }}</th>
                                        <th>Outstanding</th>                                       
                                        <th>#Quote / PI No</th>
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
        status: '',

        init() {
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            this.drawDataTable();

            $('#search').click(this.searchClick);
            $('#status').click(this.statusChange);
            $('#customer').select2({allowClear: true}).change(this.customerChange);
            $('#customer').val('').change();
        },

        searchClick() {
            Index.startDate = $('#start_date').val();
            Index.endDate =  $('#end_date').val();
            if (!Index.startDate || !Index.endDate ) 
                return alert("Date range is Required");

            $('#invoiceTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        statusChange() {
            Index.status = $(this).val();
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
                deferRender: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: "{{ route('biller.invoices.get') }}",
                    type: 'post',
                    data: {
                        start_date: this.startDate, 
                        end_date: this.endDate, 
                        customer_id: this.customerId,
                        status: this.status
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
                    {
                        data: 'tid',
                        name: 'tid'
                    },
                    {
                        data: 'notes',
                        name: 'notes'
                    },
                    {
                        data: 'invoicedate',
                        name: 'invoicedate'
                    },
                    {
                        data: 'invoiceduedate',
                        name: 'invoiceduedate'
                    },
                    {
                        data: 'total',
                        name: 'total'
                    },
                    {
                        data: 'balance',
                        name: 'balance'
                    },                    
                    {
                        data: 'quote_tid',
                        name: 'quote_tid'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        searchable: false,
                        sortable: false
                    }
                ],
                columnDefs: [
                    { type: "custom-number-sort", targets: [5, 6] },
                    { type: "custom-date-sort", targets: [3, 4] }
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