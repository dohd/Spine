@extends ('core.layouts.app')

@section('title', 'Bill Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Bill Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.utility-bills.partials.utility-bills-header-buttons')
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
                                <label for="customer">Supplier</label>
                                <select name="supplier_id" id="supplier" class="form-control" data-placeholder="Choose Supplier">
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-2">
                                <label for="bill_status">Bill Status</label>
                                <select name="bill_status" id="bill_status" class="custom-select">
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
                    <table id="billsTbl" class="table table-striped table-bordered zero-configuration" width="100%" cellpadding="0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Bill No.</th>
                                <th>Supplier</th>
                                <th>Note</th>                                
                                <th>Amount</th>
                                <th>Outstanding</th>  
                                <th>Date</th>   
                                <th>Due Date</th>                                        
                                <th>Action</th>
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
        ajaxSetup: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    const Index = {
        init() {
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());

            $('#bill_status').change(this.billStatusChange);
            $('#pmt_status').change(this.paymentStatusChange);
            $('#supplier').select2({allowClear: true}).val('').trigger('change')
            .change(this.supplierChange);

            $('#search').click(this.searchClick);
            this.drawDataTable();
        },

        searchClick() {
            Index.startDate = $('#start_date').val();
            Index.endDate =  $('#end_date').val();
            if (!Index.startDate || !Index.endDate ) 
                return alert("Date range is Required");

            $('#invoiceTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        billStatusChange() {
            const lastOpt = $('#pmt_status option:eq(-1)');
            if ($(this).val() == 'due') {
                lastOpt.addClass('d-none');
            } else lastOpt.removeClass('d-none');
                
            $('#billsTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        paymentStatusChange() {
            const lastOpt = $('#bill_status option:eq(-1)');
            if ($(this).val() == 'paid') {
                lastOpt.addClass('d-none');
            } else lastOpt.removeClass('d-none');

            $('#billsTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        supplierChange() {
            Index.customerId = $(this).val();
            $('#billsTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        drawDataTable() {
            $('#billsTbl').dataTable({
                stateSave: true,
                processing: true,
                serverSide: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: "{{ route('biller.utility-bills.get') }}",
                    type: 'POST',
                    data: {
                        start_date: Index.startDate, 
                        end_date: Index.endDate,
                        supplier_id: $('#supplier').val(),
                        bill_status: $('#bill_status').val(),
                        payment_status: $('#pmt_status').val(),
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
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    {data: 'tid', name: 'tid'},
                    {data: 'supplier', name: 'supplier'},
                    {data: 'note', name: 'note'},                    
                    {data: 'total', name: 'total'},
                    {data: 'balance', name: 'balance'},
                    {data: 'date', name: 'date'},
                    {data: 'due_date', name: 'due_date'},
                    {data: 'actions', name: 'actions', searchable: false, sortable: false}
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        }
    };

    $(() => Index.init());
</script>
@endsection
