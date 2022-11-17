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
                                    <div class="form-group col-1">
                                        <label for="label_visibility">Label Visibility</label>
                                        <select name="col_label" id="sale_col_label" class="custom-select">
                                            <option value="visible">Visible</option>
                                            <option value="invisible">Hidden</option>
                                        </select>
                                    </div>
                                    <table id="saleTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
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
                                    <div class="form-group col-1">
                                        <label for="label_visibility">Label Visibility</label>
                                        <select name="col_label" id="purchase_col_label" class="custom-select">
                                            <option value="visible">Visible</option>
                                            <option value="invisible">Hidden</option>
                                        </select>
                                    </div>
                                    <table id="purchaseTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
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
        dataTable: {
            drawCallback: void 0,
        }
    };

    const Index = {
        taxReportId: 0,

        init() {
            this.drawSaleDataTable();
            this.drawPurchaseDataTable();

            $('#sale_col_label').change(this.saleHideColumnLabel);
            $('#purchase_col_label').change(this.purchaseHideColumnLabel);

            $('#tax_report').select2({allowClear: true}).val('').change();
            $('#tax_report').change(this.taxReportChange);
        },

        taxReportChange() {
            Index.taxReportId = $(this).val() || 0;
            $('#saleTbl').DataTable().destroy();
            $('#purchaseTbl').DataTable().destroy();
            Index.drawSaleDataTable();
            Index.drawPurchaseDataTable();
        },

        saleHideColumnLabel() {
            if (this.value == 'invisible') {
                config.dataTable.drawCallback = (settings) => {
                    $("#saleTbl thead").remove();
                }
            } else config.dataTable.drawCallback = void 0; 
            $('#saleTbl').DataTable().destroy();
            Index.drawSaleDataTable();
        },

        purchaseHideColumnLabel() {
            if (this.value == 'invisible') {
                config.dataTable.drawCallback = (settings) => {
                    $("#purchaseTbl thead").remove();
                }
            } else config.dataTable.drawCallback = void 0;
            $('#purchaseTbl').DataTable().destroy();
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
                    data: {is_sale: 1, is_purchase: 0, tax_report_id: this.taxReportId}
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    ...[
                        'pin', 'customer', 'etr_code', 'invoice_date', 'invoice_no', 'note', 'subtotal',
                        'empty_col', 'cn_invoice_no', 'cn_invoice_date',
                    ].map(v => ({data: v, name: v})),
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
                lengthMenu: [
                    [25, 50, 100, 200, -1],
                    [25, 50, 100, 200, "All"]
                ],
                ...config.dataTable,
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
                    data: {is_sale: 0, is_purchase: 1, tax_report_id: this.taxReportId}
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    ...[
                        'source', 'pin', 'supplier', 'invoice_date', 'invoice_no', 'note', 
                        'empty_col', 'subtotal',
                    ].map(v => ({data: v, name: v})),
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
                lengthMenu: [
                    [25, 50, 100, 200, -1],
                    [25, 50, 100, 200, "All"]
                ],
                ...config.dataTable,
            });
        }
    };

    $(() => Index.init());
</script>
@endsection
