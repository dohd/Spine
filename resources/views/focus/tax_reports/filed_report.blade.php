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
    };

    const Index = {
        init() {
            this.drawSaleDataTable();
            this.drawPurchaseDataTable();
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
                    data: {is_sale: 1, is_purchase: 0}
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    ...[
                        'pin', 'customer', 'etr_code', 'invoice_date', 'invoice_no', 'note', 'subtotal',
                        'empty_col', 'cn_invoice_no', 'cn_invoice_date',
                    ].map(v => ({data: v, name: v})),
                ],
                columnDefs: [
                    // { type: "custom-number-sort", targets: [4, 5] },
                    // { type: "custom-date-sort", targets: [2] }
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
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
                    data: {is_sale: 0, is_purchase: 1}
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    ...[
                        'source', 'pin', 'supplier', 'invoice_date', 'invoice_no', 'note', 
                        'empty_col', 'subtotal',
                    ].map(v => ({data: v, name: v})),
                ],
                columnDefs: [
                    // { type: "custom-number-sort", targets: [4, 5] },
                    // { type: "custom-date-sort", targets: [2] }
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
