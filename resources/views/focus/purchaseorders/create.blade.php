@extends('core.layouts.app')

@section('title', 'Purchase Order | Create')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Purchase Orders Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.purchaseorders.partials.purchaseorders-header-buttons')
                </div>
            </div>
        </div> 
    </div>    

    <div class="content-body"> 
        <div class="card">
            <div class="card-body">
                {{ Form::open(['route' => 'biller.purchaseorders.store', 'method' => 'POST']) }}
                    @include('focus.purchaseorders.form')
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
    function select2Config(url, callback) {
        return {
            ajax: {
                url,
                dataType: 'json',
                type: 'POST',
                quietMillis: 50,
                data: ({term}) => ({q: term, keyword: term}),
                processResults: callback
            }
        }
    }


    // datepicker
    $('.datepicker')
        .datepicker({format: "{{ config('core.user_date_format')}}", autoHide:true})
        .datepicker('setDate', new Date())

    // On searching supplier
    $('#supplierbox').change(function() {
        const name = $('#supplierbox option:selected').text().split(' : ')[0];
        const [id, taxId] = $(this).val().split('-');
        $('#taxid').val(taxId);
        $('#supplierid').val(id);
        $('#supplier').val(name);
    });
    // load suppliers
    const supplierUrl = "{{ route('biller.suppliers.select') }}";
    function supplierData(data) {
        return {results: data.map(v => ({id: v.id+'-'+v.taxid, text: v.name+' : '+v.email}))};
    }
    $('#supplierbox').select2(select2Config(supplierUrl, supplierData));
    // load projects dropdown
    const projectUrl = "{{ route('biller.projects.project_search') }}";
    function projectData(data) {
        return {results: data.map(v => ({id: v.id, text: v.name}))};
    }
    $("#project").select2(select2Config(projectUrl, projectData));
    
    // On Tax change
    let taxIndx = 0;
    $('#tax').change(function() {
        if (taxIndx > 0) return;
        const tax = $(this).val();
        $('#rowtax-0').val(tax).change();
        $('#expvat-0').val(tax).change();
        $('#assetvat-0').val(tax).change();
        taxIndx++;
    });

    // On project change
    $("#project").change(function() {
        const projectText = $("#project option:selected").text().replace(/\s+/g, ' ');
        $('#projectexptext-0').val(projectText);
        $('#projectexpval-0').val($(this).val());
    });

    // Update transaction table
    const sumLine = (...values) => values.reduce((prev, curr) => prev + curr.replace(/,/g, '')*1, 0);
    function transxnCalc() {
        $('#transxnTbl tbody tr').each(function() {
            let total;
            switch ($(this).index()*1) {
                case 0:
                    $(this).find('td:eq(1)').text($('#stock_subttl').val());
                    $(this).find('td:eq(2)').text($('#exp_subttl').val());
                    $(this).find('td:eq(3)').text($('#asset_subttl').val());
                    total = sumLine($('#stock_subttl').val(), $('#exp_subttl').val(), $('#asset_subttl').val());
                    $('#paidttl').val(total.toLocaleString());
                    $(this).find('td:eq(4)').text($('#paidttl').val());
                    break;
                case 1:
                    $(this).find('td:eq(1)').text($('#stock_tax').val());
                    $(this).find('td:eq(2)').text($('#exp_tax').val());
                    $(this).find('td:eq(3)').text($('#asset_tax').val());
                    total = sumLine($('#stock_tax').val(), $('#exp_tax').val(), $('#asset_tax').val());
                    $('#grandtax').val(total.toLocaleString());
                    $(this).find('td:eq(4)').text($('#grandtax').val());
                    break;
                case 2:
                    $(this).find('td:eq(1)').text($('#stock_grandttl').val());
                    $(this).find('td:eq(2)').text($('#exp_grandttl').val());
                    $(this).find('td:eq(3)').text($('#asset_grandttl').val());
                    total = sumLine($('#stock_grandttl').val(), $('#exp_grandttl').val(), $('#asset_grandttl').val());
                    $('#grandttl').val(total.toLocaleString());
                    $(this).find('td:eq(4)').text($('#grandttl').val());
                    break;
            }
        });
    }

    // Tax condition
    function taxRule(id, tax) {
        $('#'+ id +' option').each(function() {
            const itemtax = $(this).val();
            $(this).removeClass('d-none');
            if (itemtax != tax && itemtax != 0) $(this).addClass('d-none');
            $(this).attr('selected', false);
            if (itemtax == tax) $(this).attr('selected', true).change();
        }); 
    }


    /**
     * Stock Tab
     */
    let stockRowId = 0;
    const stockHtml = [$('#stockTbl tbody tr:eq(0)').html(), $('#stockTbl tbody tr:eq(1)').html()];
    const stockUrl = "{{ route('biller.products.quote_product_search') }}"
    $('.stockname').autocomplete(predict(stockUrl, stockSelect));
    $('#rowtax-0').mousedown(function() { taxRule('rowtax-0', $('#tax').val()); });
    $('#stockTbl').on('click', '#addstock, .remove', function() {
        if ($(this).is('#addstock')) {
            stockRowId++;
            const i = stockRowId;
            const html = stockHtml.reduce((prev, curr) => {
                const text = curr.replace(/-0/g, '-'+i).replace(/d-none/g, '');
                return prev + '<tr>' + text + '</tr>';
            }, '');

            $('#stockTbl tbody tr:eq(-3)').before(html);
            $('.stockname').autocomplete(predict(stockUrl, stockSelect));
            taxRule('rowtax-'+i, $('#tax').val());
        }

        if ($(this).is('.remove')) {
            const $tr = $(this).parents('tr:first');
            $tr.next().remove();
            $tr.remove();
            calcStock();
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
        $tr.find('.stocktaxr').val(taxable.toLocaleString());
        $tr.find('.stockamountr').val(amount.toLocaleString());
        calcStock();

        if ($(this).is('.price')) {
            $tr.find('.uom').attr('required', true);
            $tr.next().find('.descr').attr('required', true);
        }
    });
    function calcStock() {
        let tax = 0;
        let grandTotal = 0;
        $('#stockTbl tbody tr').each(function() {
            if (!$(this).find('.qty').val()) return;
            const qty = $(this).find('.qty').val();
            const price = $(this).find('.price').val().replace(/,/g, '') || 0;
            const rowtax = $(this).find('.rowtax').val()/100 + 1;

            const amount = qty * price * rowtax;
            const taxable = amount - qty * price;
            tax += parseFloat(taxable.toFixed(2));
            grandTotal += parseFloat(amount.toFixed(2));
        });
        $('#invtax').text(tax.toLocaleString());
        $('#stock_tax').val(tax.toLocaleString());
        $('#stock_grandttl').val(grandTotal.toLocaleString());
        $('#stock_subttl').val((grandTotal - tax).toLocaleString());
        transxnCalc();
    }

    // stock select autocomplete
    let stockNameRowId = 0;
    function stockSelect(event, ui) {
        const {data} = ui.item;
        const i = stockNameRowId;
        $('#stockitemid-'+i).val(data.id);
        $('#stockdescr-'+i).val(data.name);
        const price = data.purchase_price.replace(/,/g, '') * 1;
        $('#price-'+i).val(price.toLocaleString()).change();
    }
    $('#stockTbl').on('mouseup', '.stockname', function() {
        const id = $(this).attr('id').split('-')[1];
        if ($(this).is('.stockname')) stockNameRowId = id;
    });    

    /**
     * Expense Tab
     */
    let expRowId = 0;
    const expHtml = [$('#expTbl tbody tr:eq(0)').html(), $('#expTbl tbody tr:eq(1)').html()];
    const expUrl = "{{ route('biller.accounts.account_search') }}?type=Expense";
    $('.accountname').autocomplete(predict(expUrl, expSelect));
    $('.projectexp').autocomplete(predict(projectUrl, projectExpSelect));
    $('#expvat-0').mousedown(function() { taxRule('expvat-0', $('#tax').val()); });
    $('#expTbl').on('click', '#addexp, .remove', function() {
        if ($(this).is('#addexp')) {
            expRowId++;
            const i = expRowId;
            const html = expHtml.reduce((prev, curr) => {
                const text = curr.replace(/-0/g, '-'+i).replace(/d-none/g, '');
                return prev + '<tr>' + text + '</tr>';
            }, '');

            $('#expTbl tbody tr:eq(-3)').before(html);
            $('.accountname').autocomplete(predict(expUrl, expSelect));
            $('.projectexp').autocomplete(predict(projectUrl, projectExpSelect));
            const projectText = $("#project option:selected").text().replace(/\s+/g, ' ');
            $('#projectexptext-'+i).val(projectText);
            $('#projectexpval-'+i).val($("#project").val());
            taxRule('expvat-'+i, $('#tax').val());
        }
        if ($(this).is('.remove')) {
            const $tr = $(this).parents('tr:first');
            $tr.next().remove();
            $tr.remove();
            calcExp();
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
        $tr.find('.exptaxr').val(taxable.toLocaleString());
        $tr.find('.expamountr').val(amount.toLocaleString());
        calcExp();

        if ($(this).is('.exp_price')) {
            $tr.next().find('.descr').attr('required', true);
        }
    });
    function calcExp() {
        let tax = 0;
        let totalInc = 0;
        $('#expTbl tbody tr').each(function() {
            if (!$(this).find('.exp_qty').val()) return;
            const qty = $(this).find('.exp_qty').val();
            const price = $(this).find('.exp_price').val().replace(/,/g, '') || 0;
            const rowtax = $(this).find('.exp_vat').val()/100 + 1;

            const amount = qty * price * rowtax;
            const taxable = amount - qty * price;
            tax += parseFloat(taxable.toFixed(2));
            totalInc += parseFloat(amount.toFixed(2));
        });
        $('#exprow_taxttl').text(tax.toLocaleString());
        $('#exp_tax').val(tax.toLocaleString());
        $('#exp_subttl').val((totalInc - tax).toLocaleString());
        $('#exp_grandttl').val((totalInc).toLocaleString());
        transxnCalc();
    }

    // account and project autocomplete
    let accountRowId = 0;
    function expSelect(event, ui) {
        const {data} = ui.item;
        const i = accountRowId;
        $('#expitemid-'+i).val(data.id);
    }
    function projectExpSelect(event, ui) {
        const {data} = ui.item;
        const i = expRowId;
        $('#projectexpval-'+i).val(data.id);
    }
    $('#expTbl').on('mouseup', '.accountname', function() {
        const id = $(this).attr('id').split('-')[1];
        if ($(this).is('.accountname')) accountRowId = id;
    });

    /**
     * Asset tab
     */
    let assetRowId = 0;
    const assetHtml = [$('#assetTbl tbody tr:eq(0)').html(), $('#assetTbl tbody tr:eq(1)').html()];
    const assetUrl = "{{ route('biller.assetequipments.product_search') }}";
    $('.assetname').autocomplete(predict(assetUrl, assetSelect));
    $('#assetvat-0').mousedown(function() { taxRule('assetvat-0', $('#tax').val()); });
    $('#assetTbl').on('click', '#addasset, .remove', function() {
        if ($(this).is('#addasset')) {
            assetRowId++;
            const i = assetRowId;
            const html = assetHtml.reduce((prev, curr) => {
                const text = curr.replace(/-0/g, '-'+i).replace(/d-none/g, '');
                return prev + '<tr>' + text + '</tr>';
            }, '');

            $('#assetTbl tbody tr:eq(-3)').before(html);
            $('.assetname').autocomplete(predict(assetUrl, assetSelect));
            taxRule('assetvat-'+i, $('#tax').val());
        }
        if ($(this).is('.remove')) {
            const $tr = $(this).parents('tr:first');
            $tr.next().remove();
            $tr.remove();
            calcAsset();
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
        $tr.find('.assettaxr').val(taxable.toLocaleString());
        $tr.find('.assetamountr').val(amount.toLocaleString());
        calcAsset();

        if ($(this).is('.asset_price')) {
            $tr.next().find('.descr').attr('required', true);
        }
    });
    function calcAsset() {
        let tax = 0;
        let totalInc = 0;
        $('#assetTbl tbody tr').each(function() {
            if (!$(this).find('.asset_qty').val()) return;
            const qty = $(this).find('.asset_qty').val();
            const price = $(this).find('.asset_price').val().replace(/,/g, '') || 0;
            const rowtax = $(this).find('.asset_vat').val()/100 + 1;
            
            const amount = qty * price * rowtax;
            const taxable = amount -  qty * price;
            tax += parseFloat(taxable.toFixed(2));
            totalInc += parseFloat(amount.toFixed(2));
        });
        $('#assettaxrow').text(tax.toLocaleString());
        $('#asset_tax').val(tax.toLocaleString());
        $('#asset_subttl').val((totalInc - tax).toLocaleString());
        $('#asset_grandttl').val((totalInc).toLocaleString());
        transxnCalc();
    }

    // asset and project autocomplete
    let assetNameRowId = 0;
    function assetSelect(event, ui) {
        const {data} = ui.item;
        const i = assetNameRowId;
        $('#assetitemid-'+i).val(data.id);
        $('#assetprice-'+i).val(0).change();
    } 
    $('#assetTbl').on('mouseup', '.assetname', function() {
        const id = $(this).attr('id').split('-')[1];
        if ($(this).is('.assetname')) assetNameRowId = id;
    });    

    // autocomplete config method
    function predict(url, callback) {
        return {
            source: function(request, response) {
                $.ajax({
                    url,
                    dataType: "json",
                    method: "POST",
                    data: {keyword: request.term, pricegroup_id: $('#pricegroup_id').val()},
                    success: function(data) {
                        response(data.map(v => ({
                            label: v.name,
                            value: v.name,
                            data: v
                        })));
                    }
                });
            },
            autoFocus: true,
            minLength: 0,
            select: callback
        };
    }
</script>
@endsection
