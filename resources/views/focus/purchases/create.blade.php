@extends('core.layouts.app')

@section('title', 'Purchases | Create')

@section('content')
<div class="content-wrapper">
    <div class="content-body"> 
        <div class="card">
            <div class="card-body">
                {{ Form::open(['route' => 'biller.purchases.store', 'method' => 'POST', 'id' => 'data_form']) }}
                    <div class="row">
                        <div class="col-sm-6 cmp-pnl">
                            <div id="customerpanel" class="inner-cmp-pnl">
                                <h3 class="title">Bill </h3>                                                                
                                <div class="form-group row">
                                    <div class="col-5">
                                        <div><label for="supplier-type">Select Supplier Type</label></div>
                                        <div class="d-inline-block custom-control custom-checkbox mr-1">
                                            <input type="radio" class="custom-control-input bg-primary" name="supplier_type" id="colorCheck1" value="walk-in" checked>
                                            <label class="custom-control-label" for="colorCheck1">Walkin</label>
                                        </div>
                                        <div class="d-inline-block custom-control custom-checkbox mr-1">
                                            <input type="radio" class="custom-control-input bg-purple" name="supplier_type" value="supplier" id="colorCheck3">
                                            <label class="custom-control-label" for="colorCheck3">{{trans('suppliers.supplier')}}</label>
                                        </div>
                                    </div>
                                    <div class="col-7">
                                            <label for="payer" class="caption">Search Supplier</label>                                       
                                            <select class="form-control" id="supplierbox" data-placeholder="Search Supplier" disabled></select>
                                            <input type="hidden" name="supplier_id" value="0" id="supplierid">
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <div class="col-sm-8">
                                        <label for="payer" class="caption">Supplire Name*</label>
                                        <div class="input-group ">
                                            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>                                            
                                            {{ Form::text('supplier', null, ['class' => 'form-control round', 'placeholder' => 'Supplier Name', 'id' => 'supplier', 'required']) }}
                                        </div>
                                    </div>
                                    <div class="col-sm-4"><label for="taxid" class="caption">Tax ID</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                                            {{ Form::text('supplier_taxid', null, ['class' => 'form-control round', 'placeholder' => 'Tax Id', 'id'=>'taxid', 'required']) }}
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <table class="table-responsive tfr" id="transxnTbl">
                                        <thead>
                                            <tr class="item_header bg-gradient-directional-blue white">
                                                @foreach (['Item', 'Inventory Item', 'Expenses', 'Asset & Equipments', 'Total'] as $val)
                                                    <th width="20%" class="text-center">{{ $val }}</th>
                                                @endforeach                                                  
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="text-center">Line Total</td>
                                                @for ($i = 0; $i < 4; $i++)
                                                    <td class="text-center">0.00</td>
                                                @endfor                                                
                                            </tr>                                                  
                                            <tr>
                                                <td class="text-center">Tax</td>
                                                @for ($i = 0; $i < 4; $i++)
                                                    <td class="text-center">0.00</td>
                                                @endfor                                                
                                            </tr>
                                            <tr>
                                                <td class="text-center">Grand Total</td>
                                                @for ($i = 0; $i < 4; $i++)
                                                    <td class="text-center">0.00</td>
                                                @endfor                                                                                                      
                                            </tr>
                                            <tr class="sub_c" style="display: table-row;">
                                                <td align="right" colspan="3">
                                                    @foreach (['paid_ttl', 'grand_tax', 'grand_ttl'] as $val)
                                                        <input type="hidden" name="{{ $val }}" id="{{ $val }}" value="0"> 
                                                    @endforeach 
                                                    {{ Form::submit('Post Transaction', ['class' => 'btn btn-success sub-btn btn-lg']) }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 cmp-pnl">
                            <div class="inner-cmp-pnl">
                                <h3 class="title">{{trans('purchaseorders.properties')}}</h3>
                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        <label for="tid" class="caption">Transaction ID*</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                            {{ Form::number('transxn_ref', @$last_id->tid+1, ['class' => 'form-control round']) }}
                                        </div>
                                    </div>
                                    <div class="col-sm-4"><label for="transaction_date" class="caption">Purchase Date*</label>
                                        <div class="input-group">                                            
                                            {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'transaction_date', 'data-date-auto-close']) }}
                                        </div>
                                    </div>
                                    <div class="col-sm-4"><label for="due_date" class="caption">Due Date*</label>
                                        <div class="input-group">                                            
                                            {{ Form::text('due_date', null, ['class' => 'form-control datepicker', 'id' => 'due_date']) }}
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-sm-4"><label for="ref_type" class="caption">Document Type*</label>
                                        <div class="input-group">                                            
                                            <select class="form-control" name="doc_ref_type" id="ref_type" required>
                                                <option value="">-- Select Type --</option>
                                                @foreach (['Invoice', 'Receipt', 'DNote', 'Voucher'] as $val)
                                                    <option value="{{ $val }}">{{ $val }}</option>
                                                @endforeach                                                        
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <label for="refer_no" class="caption">{{trans('general.reference')}} No.</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>                                            
                                            {{ Form::text('doc_ref', null, ['class' => 'form-control round', 'placeholder' => trans('general.reference'), 'required']) }}
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <label for="taxFormat" class="caption">{{trans('general.tax')}}*</label>
                                        <select class="form-control" name="tax" id="tax">
                                            @foreach ($additionals as $tax)
                                                <option value="{{ (int) $tax->value }}" {{ $tax->is_default ? 'selected' : ''}}>
                                                    {{ $tax->name }} 
                                                </option>
                                            @endforeach                                                    
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="project" class="caption">Projects</label>
                                            <select class="form-control" name="project_id" id="project" required>
                                                <option value="">-- Select Project --</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <label for="toAddInfo" class="caption">{{trans('general.note')}}*</label>
                                        {{ Form::textarea('note', null, ['class' => 'form-control', 'placeholder' => trans('general.note'), 'rows'=>'2', 'required']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Menus -->
                    <ul class="nav nav-tabs nav-top-border no-hover-bg nav-justified" role="tablist">
                        <li class="nav-item bg-gradient-directional-blue">
                            <a class="nav-link active" id="active-tab1" data-toggle="tab" href="#active1" aria-controls="active1" role="tab" aria-selected="true">Inventory/Stock Items</a>
                        </li>
                        <li class="nav-item bg-danger">
                            <a class="nav-link " id="active-tab2" data-toggle="tab" href="#active2" aria-controls="active2" role="tab">Expenses</a>
                        </li>
                        <li class="nav-item bg-success">
                            <a class="nav-link " id="active-tab3" data-toggle="tab" href="#active3" aria-controls="active3" role="tab">Assets & Equipments</a>
                        </li>
                    </ul>
                    <div class="tab-content px-1 pt-1">
                        <!-- tab1 -->
                        @include('focus.purchases.partials.stock_tab')
                        <!-- tab2 -->
                        @include('focus.purchases.partials.expense_tab')
                        <!-- tab3 -->
                        @include('focus.purchases.partials.asset_tab')
                    </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});
    const ajaxConfig = {
        dataType: 'json',
        type: 'POST',
        quietMillis: 50,
        data: function({term}) { 
            return { q: term }
        },
    };

    // datepicker
    $('.datepicker')
        .datepicker({format: "{{ config('core.user_date_format')}}"})
        .datepicker('setDate', new Date())
        .change(function() { $(this).datepicker('hide') });
    
    // On clicking supplier_type
    $("input[type='radio']").change(function() {
        $('#supplierbox').html('').attr('disabled', true);
        $('#taxid').val('').attr('readonly', false);
        $('#supplier').val('').attr('readonly', false);
        if ($(this).val() == 'supplier') {
            $('#supplierbox').attr('disabled', false);
            $('#taxid').attr('readonly', true);
            $('#supplier').attr('readonly', true);
        }
    });
    $('#supplierbox').change(function() {
        const name = $('#supplierbox option:selected').text().split(' : ')[0];
        const [id, taxId] = $(this).val().split('-');
        $('#taxid').val(taxId);
        $('#supplierid').val(id);
        $('#supplier').val(name);
    });
    // load suppliers
    $('#supplierbox').select2({
        ajax: {
            url: "{{ route('biller.suppliers.select') }}",
            processResults: function(data) {
                return {results: data.map(v => ({id: v.id+'-'+v.taxid, text: v.name+' : '+v.email}))};
            },
            ...ajaxConfig
        }
    });
    // load projects dropdown
    $("#project").select2({
        ajax: {
            url: "{{ route('biller.projects.project_search') }}",
            processResults: function(data) {
                return {results: data.map(v => ({id: v.id, text: v.name}))};
            },
            ...ajaxConfig
        }
    });
    // On Tax change
    let taxIndx = 0;
    $('#tax').change(function() {
        if (taxIndx > 0) return;
        const tax = $(this).val();
        taxRule(0, tax);
        $('#expvat-0').val(tax);
        $('#assetvat-0').val(tax);
        taxIndx++;
    });


    /**
     * Stock Tab
     */
    let stockRowId = 0;
    const stockHtml = [$('#stockTbl tbody tr:eq(0)').html(), $('#stockTbl tbody tr:eq(1)').html()];
    $('.stockname').autocomplete(stockPredict(stockRowId));
    $('#rowtax-0').mousedown(function() {
        taxRule(0, $('#tax').val());                      
    });
    $('#stockTbl').on('click', '#addstock, .remove', function() {
        if ($(this).is('#addstock')) {
            stockRowId++;
            const i = stockRowId;
            const html = stockHtml.reduce((prev, curr) => {
                const text = curr.replace(/-0/g, '-'+i).replace(/d-none/g, '');
                return prev + '<tr>' + text + '</tr>';
            }, '');

            $('#stockTbl tbody tr:eq(-3)').before(html);
            $('.stockname').autocomplete(stockPredict(i));
            taxRule(i, $('#tax').val());
        }

        if ($(this).is('.remove')) {
            const $tr = $(this).parents('tr:first');
            $tr.next().remove();
            $tr.remove();
        }    
    })
    $('#stockTbl').on('change', '.qty, .price, .rowtax', function() {
        const $tr = $(this).parents('tr:first');
        const qty = $tr.find('.qty').val();
        const price = $tr.find('.price').val().replace(/,/g, '') || 0;
        const rowtax = $tr.find('.rowtax').val()/100 + 1;
        const amount = qty * price * rowtax;
        const taxable = amount - (qty * price);

        $tr.find('.price').val((price*1).toLocaleString());
        $tr.find('.amount').text(amount.toLocaleString());
        $tr.find('.taxable').val(taxable.toLocaleString());
        calcStock();
    });
    function calcStock() {
        let tax = 0;
        let grandTotal = 0;
        $('#stockTbl tbody tr').each(function() {
            if (!$(this).find('.qty').val()) return;
            const qty = $(this).find('.qty').val();
            const price = $(this).find('.price').val().replace(/,/g, '') || 0;
            const rowtax = $(this).find('.rowtax').val()/100 + 1;
            const amountInc = qty * price * rowtax;
            const amountExc = qty * price;
            tax += (amountInc - amountExc);
            grandTotal += amountInc;
        });
        $('#invtax').text(tax.toLocaleString());
        $('#stock_tax').val(tax.toLocaleString());
        $('#stock_grandttl').val(grandTotal.toLocaleString());
        $('#stock_subttl').val((grandTotal - tax).toLocaleString());
        transxnCalc();
    }
    // Tax condition
    function taxRule(i, tax) {
        $('#rowtax-'+ i +' option').each(function() {
            const rowtax = $(this).val();
            $(this).removeClass('d-none');
            if (rowtax != tax && rowtax != 0) $(this).addClass('d-none');
            $(this).attr('selected', false);
            if (rowtax == tax) $(this).attr('selected', true).change();
        }); 
    }

    function stockPredict(i) {
        return {
            source: function(request, response) {
                $.ajax({
                    url: baseurl + 'products/quotesearch/1',
                    dataType: "json",
                    method: 'POST',
                    data: 'keyword=' + request.term,                        
                    success: function(data) {
                        response($.map(data, function(item) {
                            return {
                                label: item.name,
                                value: item.name,
                                data: item
                            };
                        }));
                    }
                });
            },
            autoFocus: true,
            minLength: 0,
            select: function(event, ui) {
                const {data} = ui.item;
                const price = parseFloat(data.purchase_price).toLocaleString();
                $('#price-'+i).val(price).change();
            }
        };
    }

    
    /**
     * Expense Tab
     */
    let expRowId = 0;
    const expHtml = [$('#expTbl tbody tr:eq(0)').html(), $('#expTbl tbody tr:eq(1)').html()];
    $('#expTbl').on('click', '#addexp, .remove', function() {
        if ($(this).is('#addexp')) {
            expRowId++;
            const i = expRowId;
            const html = expHtml.reduce((prev, curr) => {
                const text = curr.replace(/-0/g, '-'+i).replace(/d-none/g, '');
                return prev + '<tr>' + text + '</tr>';
            }, '');

            $('#expTbl tbody tr:eq(-3)').before(html);
            $('#expvat-'+i).val($('#tax').val());
        }
        if ($(this).is('.remove')) {
            const $tr = $(this).parents('tr:first');
            $tr.next().remove();
            $tr.remove();
        }    
    });
    $('#expTbl').on('change', '.exp_qty, .exp_price, .exp_vat', function() {
        const $tr = $(this).parents('tr:first');
        const qty = $tr.find('.exp_qty').val();
        const price = $tr.find('.exp_price').val().replace(/,/g, '') || 0;
        const rowtax = $tr.find('.exp_vat').val()/100 + 1;
        const amount = qty * price * rowtax;
        const taxable = amount - (qty * price);

        $tr.find('.exp_price').val((price*1).toLocaleString());
        $tr.find('.exp_tax').text(taxable.toLocaleString());
        $tr.find('.exp_amount').text(amount.toLocaleString());
        calcExp();
    });
    function calcExp() {
        let tax = 0;
        let totalInc = 0;
        $('#expTbl tbody tr').each(function() {
            if (!$(this).find('.exp_qty').val()) return;
            const qty = $(this).find('.exp_qty').val();
            const price = $(this).find('.exp_price').val().replace(/,/g, '') || 0;
            const rowtax = $(this).find('.exp_vat').val()/100 + 1;
            const amountInc = qty * price * rowtax;
            const amountExc = qty * price;
            tax += (amountInc - amountExc);
            totalInc += amountInc;
        });
        $('#exprow_taxttl').text(tax.toLocaleString());
        $('#exp_tax').val(tax.toLocaleString());
        $('#exp_subttl').val((totalInc - tax).toLocaleString());
        $('#exp_grandttl').val((totalInc).toLocaleString());
        transxnCalc();
    }


    /**
     * Asset tab
     */
    let assetRowId = 0;
    const assetHtml = [$('#assetTbl tbody tr:eq(0)').html(), $('#assetTbl tbody tr:eq(1)').html()];
    $('#assetTbl').on('click', '#addasset, .remove', function() {
        if ($(this).is('#addasset')) {
            assetRowId++;
            const i = assetRowId;
            const html = assetHtml.reduce((prev, curr) => {
                const text = curr.replace(/-0/g, '-'+i).replace(/d-none/g, '');
                return prev + '<tr>' + text + '</tr>';
            }, '');

            $('#assetTbl tbody tr:eq(-3)').before(html);
            $('#assetvat-'+i).val($('#tax').val());
        }
        if ($(this).is('.remove')) {
            const $tr = $(this).parents('tr:first');
            $tr.next().remove();
            $tr.remove();
        }    
    });    
    $('#assetTbl').on('change', '.asset_qty, .asset_price, .asset_vat', function() {
        const $tr = $(this).parents('tr:first');
        const qty = $tr.find('.asset_qty').val();
        const price = $tr.find('.asset_price').val().replace(/,/g, '') || 0;
        const rowtax = $tr.find('.asset_vat').val()/100 + 1;
        const amount = qty * price * rowtax;
        const taxable = amount - (qty * price);

        $tr.find('.asset_price').val((price*1).toLocaleString());
        $tr.find('.asset_tax').text(taxable.toLocaleString());
        $tr.find('.asset_amount').text(amount.toLocaleString());
        calcAsset();
    });
    function calcAsset() {
        let tax = 0;
        let totalInc = 0;
        $('#assetTbl tbody tr').each(function() {
            if (!$(this).find('.asset_qty').val()) return;
            const qty = $(this).find('.asset_qty').val();
            const price = $(this).find('.asset_price').val().replace(/,/g, '') || 0;
            const rowtax = $(this).find('.asset_vat').val()/100 + 1;
            const amountInc = qty * price * rowtax;
            const amountExc = qty * price;
            tax += (amountInc - amountExc);
            totalInc += amountInc;
        });
        $('#assettaxrow').text(tax.toLocaleString());
        $('#asset_tax').val(tax.toLocaleString());
        $('#asset_subttl').val((totalInc - tax).toLocaleString());
        $('#asset_grandttl').val((totalInc).toLocaleString());
        transxnCalc();
    }






    // Update transaction table
    const sumLine = (...values) => values.reduce((prev, curr) => prev + curr*1, 0);
    function transxnCalc() {
        $('#transxnTbl tbody tr').each(function() {
            let total;
            switch ($(this).index()*1) {
                case 0:
                    $(this).find('td:eq(1)').text($('#stock_subttl').val());
                    $(this).find('td:eq(2)').text($('#exp_subttl').val());
                    $(this).find('td:eq(3)').text($('#asset_subttl').val());
                    total = sumLine($('#stock_subttl').val(), $('#exp_subttl').val(), $('#asset_subttl').val());
                    $('#paid_ttl').val(total.toLocaleString());
                    $(this).find('td:eq(4)').text($('#paid_ttl').val());
                    break;
                case 1:
                    $(this).find('td:eq(1)').text($('#stock_tax').val());
                    $(this).find('td:eq(2)').text($('#exp_tax').val());
                    $(this).find('td:eq(3)').text($('#asset_tax').val());
                    total = sumLine($('#stock_tax').val(), $('#exp_tax').val(), $('#asset_tax').val());
                    $('#grand_tax').val(total.toLocaleString());
                    $(this).find('td:eq(4)').text($('#grand_tax').val());
                    break;
                case 2:
                    $(this).find('td:eq(1)').text($('#stock_grandttl').val());
                    $(this).find('td:eq(2)').text($('#exp_grandttl').val());
                    $(this).find('td:eq(3)').text($('#asset_grandttl').val());
                    total = sumLine($('#stock_grandttl').val(), $('#exp_grandttl').val(), $('#asset_grandttl').val());
                    $('#grand_ttl').val(total.toLocaleString());
                    $(this).find('td:eq(4)').text($('#grand_ttl').val());
                    break;
            }
        });
    }





    // On selecting Project
    $('#project_id').select2();
    const projects = @json($projects);
    $('#project_id').change(function() {
        const text = $(this).find('option:selected').text().replace(/\s+/g, ' ');
        const len = $('#saman-row-exp').find('input[name="exp_project[]"]').length;
        // set default expense inputs
        projects.forEach(v => {
            if (v.id == $(this).val()) {
                for (let i = 0; i < len; i++) {
                    $('#exp_project-' + i).val(text);
                    $('#exp_project_id-' + i).val(v.id);
                    $('#exp_client_id-' + i).val(v.customer_id);
                    $('#exp_project_id-' + i).val(v.branch_id);
                }
            }
        });
    });
    // On clicking Expenses Add Row button
    $('#expaddproduct').click(function() {
        const text = $('#project_id option:selected').text().replace(/\s+/g, ' ');
        const len = $('#saman-row-exp').find('input[name="exp_project[]"]').length;
        const i = len - 1;
        // set default expense inputs
        projects.forEach(v => {
            if (v.id == $('#project_id').val()) {
                $('#exp_project-' + i).val(text);
                $('#exp_project_id-' + i).val(v.id);
                $('#exp_client_id-' + i).val(v.customer_id);
                $('#exp_project_id-' + i).val(v.branch_id);
            }
        });
    });
</script>
@endsection
