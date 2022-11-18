@extends ('core.layouts.app')

@section('title', 'Tax Return Report')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Tax Return Report</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.tax_reports.partials.tax-report-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-2">
                                    <label for="month">File Month</label>
                                    <select name="file_month" id="file_month" class="custom-select">
                                        @foreach (range(1,12) as $v)
                                            <option value="{{ $v }}" {{ date('m') == $v? 'selected' : '' }}>
                                                {{ DateTime::createFromFormat('!m', $v)->format('F') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-2">
                                    <label for="status">Tax Rate</label>
                                    <select name="tax_rate" id="tax_rate" class="custom-select">
                                        @foreach ($additionals as $row)
                                            <option value="{{ $row->value }}" {{ $row->default? 'selected' : '' }}>
                                                {{ $row->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-5">
                                    <label for="customer">Report Title</label>
                                    <select name="tax_report_id" class="form-control" id="tax_report" data-placeholder="Choose Report">
                                        @foreach ($tax_reports as $row)
                                            <option value="{{ $row->id }}">
                                                {{ $row->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-2">
                                    <label for="customer">File Status</label>
                                    <select name="is_filed" class="custom-select" id="is_filed">
                                        @foreach ([0,1] as $val)
                                            <option value="{{ $val }}" {{ $val? 'selected' : '' }}>
                                                {{ $val? 'Filed' : 'Removed' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            {{-- tab menu --}}
                            <ul class="nav nav-tabs nav-top-border no-hover-bg" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="active-tab1" data-toggle="tab" href="#active1" aria-controls="active1" role="tab" aria-selected="true">Sales</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link " id="active-tab2" data-toggle="tab" href="#active2" aria-controls="active2" role="tab">Purchases</a>
                                </li>                                     
                            </ul>
                            <div class="tab-content px-1 pt-1">
                                {{-- sales tab --}}
                                <div class="tab-pane active in" id="active1" aria-labelledby="active-tab1" role="tabpanel">
                                    
                                    <table id="saleTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                {{-- <th>#</th> --}}
                                                <th>Pin</th>
                                                <th>Buyer</th>
                                                <th>ETR Code</th>
                                                <th>Invoice Date</th>
                                                <th>Invoice No.</th>
                                                <th>Description</th>
                                                <th>Taxable Amount</th>
                                                <th>&nbsp;</th>
                                                <th>CN Invoice No.</th>
                                                <th>CN Invoice Date</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>                      
                                    </table>
                                </div>
                                {{-- purchases tab --}}
                                <div class="tab-pane" id="active2" aria-labelledby="link-tab2" role="tabpanel">
                                    
                                    <table id="purchaseTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                {{-- <th>#</th> --}}
                                                <th>Source</th>
                                                <th>Pin</th>
                                                <th>Supplier</th>
                                                <th>Invoice Date</th>
                                                <th>Invoice No.</th>
                                                <th>Description</th>
                                                <th>&nbsp;</th>
                                                <th>Taxable Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>                        
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
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
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    const Index = {
        taxReportId: @json(request('tax_report_id')),
        isFiled: 1,
        fileMonth: '',
        taxRate: '',

        init() {
            this.drawSaleDataTable();
            this.drawPurchaseDataTable();

            $('#sale_col_label').change(this.saleHideColumnLabel);
            $('#purchase_col_label').change(this.purchaseHideColumnLabel);
            $('#is_filed').change(this.fileStatusChange);
            $('#file_month').change(this.fileMonthChange);
            $('#tax_rate').change(this.taxRateChange);

            $('#tax_report').select2({allowClear: true});
            if (this.taxReportId) {
                $('#tax_report').val(this.taxReportId);
            } else {
                $('#tax_report').val('').change();
            }
            
            $('#tax_report').change(this.taxReportChange);
        },

        taxReportChange() {
            Index.taxReportId = $(this).val() || 0;
            Index.reloadDataTable();
        },

        fileStatusChange() {
            Index.isFiled = $(this).val();
            Index.reloadDataTable();
        },

        fileMonthChange() {
            Index.fileMonth = $(this).val();
            Index.reloadDataTable();
        },

        taxRateChange() {
            Index.taxRate = $(this).val();
            Index.reloadDataTable();
        },

        reloadDataTable() {
            $('#saleTbl').DataTable().destroy();
            $('#purchaseTbl').DataTable().destroy();
            Index.drawSaleDataTable();
            Index.drawPurchaseDataTable();
        },

        drawSaleDataTable() {
            $('#saleTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: "{{ route('biller.tax_reports.get_filed_items') }}",
                    type: 'POST',
                    data: {
                        is_sale: 1, 
                        is_purchase: 0, 
                        tax_report_id: this.taxReportId,
                        is_filed: this.isFiled,
                        file_month: this.fileMonth,
                        tax_rate: this.taxRate,
                    },
                    dataSrc: ({data}) => {
                        // set etr code
                        data = data.map(v => {
                            v['etr_code'] = @json($company->etr_code);
                            return v;
                        });
                        return data;
                    },
                },
                columns: [
                    // {data: 'DT_Row_Index', name: 'id'},
                    ...[
                        'pin', 'customer', 'etr_code', 'invoice_date', 'invoice_no', 'note', 'subtotal',
                        'empty_col', 'cn_invoice_no', 'cn_invoice_date',
                    ].map(v => ({data: v, name: v})),
                ],
                order: [[3, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
                lengthMenu: [
                    [25, 50, 100, 200, -1],
                    [25, 50, 100, 200, "All"]
                ],
            });
        },

        drawPurchaseDataTable() {
            $('#purchaseTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: "{{ route('biller.tax_reports.get_filed_items') }}",
                    type: 'POST',
                    data: {
                        is_purchase: 1, 
                        is_sale: 0, 
                        tax_report_id: this.taxReportId,
                        is_filed: this.isFiled,
                        file_month: this.fileMonth,
                        tax_rate: this.taxRate,
                    },
                },
                columns: [
                    // {data: 'DT_Row_Index', name: 'id'},
                    ...[
                        'source', 'pin', 'supplier', 'invoice_date', 'invoice_no', 'note', 
                        'empty_col', 'subtotal',
                    ].map(v => ({data: v, name: v})),
                ],
                order: [[3, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
                lengthMenu: [
                    [25, 50, 100, 200, -1],
                    [25, 50, 100, 200, "All"]
                ],
            });
        }
    };

    $(() => Index.init());
</script>
@endsection
