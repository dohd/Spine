@extends('core.layouts.app')

@section('title', 'Purchases | Create')

@section('content')
<div class="content-wrapper">
    <div class="content-body"> 
        <div class="card">
            <div class="card-body">
                {{ Form::open(['route' => 'biller.purchases.store', 'method' => 'POST', 'id' => 'data_form']) }}
                    @include('focus.purchases.form')
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
