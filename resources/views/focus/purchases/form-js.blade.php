<!-- properties -->
<script>
    // ajax
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});

    // select2 config
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
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format')}}", autoHide: true})        
    .datepicker('setDate', new Date());
        
    // On selecting supplier type
    $("input[name='supplier_type']").change(function() {
        $('#supplierbox').html('').attr('disabled', true);
        $('#taxid').val('').attr('readonly', false);
        $('#supplier').val('').attr('readonly', false);
        $('#supplierid').val(1);
        if ($(this).val() == 'supplier') {
            $('#supplierbox').attr('disabled', false);
            $('#taxid').attr('readonly', true);
            $('#supplier').attr('readonly', true);
        }
    });

    // On searching supplier
    $('#supplierbox').change(function() {
        const name = $('#supplierbox option:selected').text().split(' : ')[0];
        const [id, taxId] = $(this).val().split('-');
        $('#taxid').val(taxId);
        $('#supplierid').val(id);
        $('#supplier').val(name);
        let priceCustomer = '';
            $('#pricegroup_id option').each(function () {
                if (id == $(this).val())
                priceCustomer = $(this).val();
            });
            
            $('#pricegroup_id').val(priceCustomer);
    });

    // load suppliers
    const supplierUrl = "{{ route('biller.suppliers.select') }}";
    function supplierData(data) {
        return {results: data.map(v => ({id: `${v.id}-${v.taxid || ''}`, text: `${v.name} : ${v.email}`}))};
    }
    $('#supplierbox').select2(select2Config(supplierUrl, supplierData));

    // load projects dropdown
    const projectUrl = "{{ route('biller.projects.project_search') }}";
    function projectData(data) {
        data = [{id: 0, name: 'None'}].concat(data).map(v => ({id: v.id, text: v.name}));
        return {results: data};
    }
    $("#project").select2(select2Config(projectUrl, projectData));
    
    // On Tax change
    let taxChangeCount = 0;
    $('#tax').change(function() {
        if (taxChangeCount > 0) return;
        const tax = $(this).val();
        $('#rowtax-0').val(tax).change();
        $('#expvat-0').val(tax).change();
        $('#assetvat-0').val(tax).change();
        taxChangeCount++;
    });

    // On project change
    $("#project").change(function() {
        const text = $("#project option:selected").text().replace(/\s+/g, ' ');
        $('#projectexptext-0').val(text);
        $('#projectexpval-0').val($(this).val());
        $('#projectstocktext-0').val(text);
        $('#projectstockval-0').val($(this).val());
        $('#projectassettext-0').val(text);
        $('#projectassetval-0').val($(this).val());
    });

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

    // on Tax on amount change
    $('.is_tax_exc').change(function() {
        $('#qty-0').change();
        $('#expqty-0').change();
        $('#assetqty-0').change();
    });

    // edit mode
    let countPurchaseItems = @json(@$purchase->products? $purchase->products->count() : 0);    
</script>

<!-- post transaction totals table-->
<script>
    const sumLine = (...values) => values.reduce((prev, curr) => prev + curr.replace(/,/g, '')*1, 0);
    function transxnCalc() {
        $('#transxnTbl tbody tr').each(function() {
            let total;
            switch ($(this).index()) {
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
</script>

<!-- autocomplete method -->
<script>
    function predict(url, callback) {
        return {
            source: function(request, response) {
                $.ajax({
                    url,
                    dataType: "json",
                    method: "POST",
                    data: {keyword: request.term, pricegroup_id: $('#pricegroup_id').val()},
                    success: data => {
                        // prepend default project option
                        if (url.includes('projects')) {
                            const projectOpt = {
                                id: null, 
                                name: 'None',
                                client_id: null, 
                                branch_id: null
                            };
                            data.splice(0, 0, projectOpt);
                        }
                        
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

<!-- Stock Tab -->
<script>
    const stockUrl = "{{ route('biller.products.purchase_search') }}";
    const stockHtml = [$('#stockTbl tbody tr:eq(0)').html(), $('#stockTbl tbody tr:eq(1)').html()];

    let stockRowId = 0;
    // edit mode
    if (countPurchaseItems) {
        stockRowId = countPurchaseItems;
        $('#stockTbl tbody tr:lt(2)').remove(); 
    }

    // on change project
    $('#stockTbl').on('change', '.projectstock, .warehouse', function() {
        const row = $(this).parents('tr:first');
        if ($(this).is('.projectstock')) {
            row.find('.warehouse').val('');
        } else if ($(this).is('.warehouse')) {
            if ($(this).val()*1 > 0) row.find('.projectstock').val('').attr('readonly', true);
            else row.find('.projectstock').val('').attr('readonly', false);
        }
    });
    
    $('.stockname').autocomplete(predict(stockUrl, stockSelect));
    $('.projectstock').autocomplete(predict(projectUrl, projectStockSelect));
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
            $('.projectstock').autocomplete(predict(projectUrl, projectStockSelect));
            const projectText = $("#project option:selected").text().replace(/\s+/g, ' ');
            $('#projectstocktext-'+i).val(projectText);
            $('#projectstockval-'+i).val($("#project option:selected").val());
            taxRule('rowtax-'+i, $('#tax').val());
            //Add the previous supplier data            
            let priceCustomer = '';
                $('#pricegroup_id option').each(function () {
                    if ($('#supplierid').val() == $(this).val())
                    priceCustomer = $(this).val();
                });
                
                $('#pricegroup_id').val(priceCustomer);
        }

        if ($(this).is('.remove')) {
            const $tr = $(this).parents('tr:first');
            $tr.next().remove();
            $tr.remove();
            calcStock();
        }    
    })
    $('#stockTbl').on('change', '.qty, .price, .rowtax, .uom', function() {
        const el = $(this);
        const row = el.parents('tr:first');

        const qty = accounting.unformat(row.find('.qty').val());
        const price = accounting.unformat(row.find('.price').val());
        const rowtax = 1 + row.find('.rowtax').val() / 100;

        let amount = 0;
        let taxable = 0;
        if ($('.is_tax_exc').prop('checked')) {
            amount = qty * price * rowtax;
            taxable = qty * price * (rowtax - 1);
        } else {
            amount = qty * price;
            taxable = (amount / rowtax) * (rowtax - 1);
        }

        row.find('.price').val(accounting.formatNumber(price));
        row.find('.amount').text(accounting.formatNumber(amount));
        row.find('.taxable').val(accounting.formatNumber(taxable));
        row.find('.stocktaxr').val(accounting.formatNumber(taxable));
        row.find('.stockamountr').val(accounting.formatNumber(amount));
        calcStock();

        if (el.is('.price')) {
            row.next().find('.descr').attr('required', true);
        }
        if (el.is('.uom')) {
            const purchasePrice = el.find('option:selected').attr('purchase_price');
            row.find('.price').val(purchasePrice).change();
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

            let amount = 0;
            let taxable = 0;
            if ($('.is_tax_exc:checked').val() * 1) {
                amount = qty * price * rowtax;
                taxable = amount - qty * price;
            } else {
                amount = qty * price / rowtax;
                taxable = qty * price - amount;
                amount += taxable;
            } 
            tax += parseFloat(taxable.toFixed(2));
            grandTotal += parseFloat(amount.toFixed(2));
        });

        
        $('#invtax').text(tax.toLocaleString());
        $('#stock_tax').val(tax.toLocaleString());
        $('#stock_grandttl').val(grandTotal.toLocaleString());
        $('#stock_subttl').val((grandTotal - tax).toLocaleString());
        transxnCalc();
    }

    // stock and project autocomplete
    let stockNameRowId = 0;
    let projectStockRowId = 0;
    function stockSelect(event, ui) {
        const {data} = ui.item;
        const i = stockNameRowId;
        $('#stockitemid-'+i).val(data.id);
        $('#stockdescr-'+i).val(data.name);
        
        const purchasePrice = accounting.unformat(data.purchase_price);
        $('#price-'+i).val(accounting.formatNumber(purchasePrice)).change();

        $('#uom-'+i).html('');
        if (data.units)
        data.units.forEach(v => {
            const rate = parseFloat(v.base_ratio) * purchasePrice;
            const option = `<option value="${v.code}" purchase_price="${rate}" >${v.code}</option>`;
            $('#uom-'+i).append(option);
        });
        if(data.uom){
            const option = `<option value="${data.uom}" >${data.uom}</option>`;
            $('#uom-'+i).append(option);
        }
    }
    function projectStockSelect(event, ui) {
        const {data} = ui.item;
        $('#projectstockval-'+projectStockRowId).val(data.id);
    }
    $('#stockTbl').on('mouseup', '.projectstock, .stockname', function() {
        const id = $(this).attr('id').split('-')[1];
        if ($(this).is('.projectstock')) projectStockRowId = id;
        if ($(this).is('.stockname')) stockNameRowId = id;
    });    
</script>

<!-- Expense Tab -->
<script>
    const expUrl = "{{ route('biller.accounts.account_search') }}?type=Expense";
    const expHtml = [$('#expTbl tbody tr:eq(0)').html(), $('#expTbl tbody tr:eq(1)').html()];

    let expRowId = 0;
    if (countPurchaseItems) {
        expRowId = countPurchaseItems;
        $('#expTbl tbody tr:lt(2)').remove(); 
    }
    
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
            $('#projectexpval-'+i).val($("#project option:selected").val());
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

        let amount = 0;
        let taxable = 0;
        if ($('.is_tax_exc:checked').val() * 1) {
            amount = qty * price * rowtax;
            taxable = amount - qty * price;
        } else {
            amount = qty * price / rowtax;
            taxable = qty * price - amount;
            amount += taxable;
        }

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

            let amount = 0;
            let taxable = 0;
            if ($('.is_tax_exc:checked').val() * 1) {
                amount = qty * price * rowtax;
                taxable = amount - qty * price;
            } else {
                amount = qty * price / rowtax;
                taxable = qty * price - amount;
                amount += taxable;
            }
            tax += parseFloat(taxable.toFixed(2));
            totalInc += parseFloat(amount.toFixed(2));         
        });
        $('#exprow_taxttl').text(tax.toLocaleString());
        $('#exp_tax').val(tax.toLocaleString());
        $('#exp_grandttl').val((totalInc).toLocaleString());
        $('#exp_subttl').val((totalInc - tax).toLocaleString());
        transxnCalc();
    }
    
    // account and project autocomplete
    let accountRowId = 0;
    let projectExpRowId = 0;
    function expSelect(event, ui) {
        const {data} = ui.item;
        $('#expitemid-'+accountRowId).val(data.id);
    }
    function projectExpSelect(event, ui) {
        const {data} = ui.item;
        $('#projectexpval-'+projectExpRowId).val(data.id);
    }
    $('#expTbl').on('mouseup', '.projectexp, .accountname', function() {
        const id = $(this).attr('id').split('-')[1];
        if ($(this).is('.projectexp')) projectExpRowId = id;
        if ($(this).is('.accountname')) accountRowId = id;
    });
</script>

<!-- Asset Tab -->
<script>
    const assetUrl = "{{ route('biller.assetequipments.product_search') }}";
    const assetHtml = [$('#assetTbl tbody tr:eq(0)').html(), $('#assetTbl tbody tr:eq(1)').html()];

    let assetRowId = 0;
    if (countPurchaseItems) {
        assetRowId = countPurchaseItems;
        $('#assetTbl tbody tr:lt(2)').remove(); 
    }
    
    $('.assetname').autocomplete(predict(assetUrl, assetSelect));
    $('.projectasset').autocomplete(predict(projectUrl, projectAssetSelect));
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
            $('.projectasset').autocomplete(predict(projectUrl, projectAssetSelect));
            const projectText = $("#project option:selected").text().replace(/\s+/g, ' ');
            $('#projectassettext-'+i).val(projectText);
            $('#projectassetval-'+i).val($("#project option:selected").val());
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

        let amount = 0;
        let taxable = 0;
        if ($('.is_tax_exc:checked').val() * 1) {
            amount = qty * price * rowtax;
            taxable = amount - qty * price;
        } else {
            amount = qty * price / rowtax;
            taxable = qty * price - amount;
            amount += taxable;
        }

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

            let amount = 0;
            let taxable = 0;
            if ($('.is_tax_exc:checked').val() * 1) {
                amount = qty * price * rowtax;
                taxable = amount - qty * price;
            } else {
                amount = qty * price / rowtax;
                taxable = qty * price - amount;
                amount += taxable;
            }
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
    let projectAssetRowId = 0;
    function assetSelect(event, ui) {
        const {data} = ui.item;
        const i = assetNameRowId;
        $('#assetitemid-'+i).val(data.id);
        $('#assetprice-'+i).val(0).change();
    } 
    function projectAssetSelect(event, ui) {
        const {data} = ui.item;
        $('#projectassetval-'+projectAssetRowId).val(data.id);
    }
    $('#assetTbl').on('mouseup', '.projectasset, .assetname', function() {
        const id = $(this).attr('id').split('-')[1];
        if ($(this).is('.projectasset')) projectAssetRowId = id;
        if ($(this).is('.assetname')) assetNameRowId = id;
    });    
</script>
