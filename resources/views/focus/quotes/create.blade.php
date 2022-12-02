@extends('core.layouts.app')

@php
    $header_title = trans('labels.backend.quotes.management');
    $is_pi = request('page') == 'pi';
    $task = request('task');
    if ($is_pi) $header_title = 'Proforma Invoice Management';
@endphp

@section('title', $header_title)

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="alert alert-warning col-12 d-none budget-alert" role="alert">
            <strong>E.P Margin Not Met!</strong> Check line item rates.
        </div>
        <div class="content-header-left col-6">
            <h4 class="content-header-title">{{ $header_title }}</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                @include('focus.quotes.partials.quotes-header-buttons')
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
        {{ Form::open(['route' => 'biller.quotes.store', 'method' => 'POST']) }}
            @include('focus.quotes.form')
        {{ Form::close() }}
        </div>
    </div> 
</div>
@endsection

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script>    
    $.ajaxSetup({headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }});

    $('#lead_id').select2({
        allowClear: true,
        placeholder: 'Search by No, Client, Branch, Title'
    }).val('').change();

    // initialize datepicker
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    $('#referencedate').datepicker('setDate', new Date());
    $('#date').datepicker('setDate', new Date());

    // print type
    $('input[type=radio]').change(function() {
        if ($(this).val() == 'inclusive') $('#vatText').text('(VAT-Inclusive)');
        else $('#vatText').text('(VAT-Exclusive)');
    });

    // On change lead and djc
    const subject = {title: '', djc: ''};
    $('form').on('change', '#lead_id, #reference', function() {
        if ($(this).is('#lead_id')) {
            const opt = $('#lead_id option:selected');
            $('#subject').val(opt.attr('title'));
            $('#client_ref').val(opt.attr('client_ref'));
            $('#branch_id').val(opt.attr('branch_id'));
            $('#customer_id').val(opt.attr('customer_id'));
            subject.title = opt.attr('title');

            // update price customer based on selected lead
            let priceCustomer = '';
            $('#price_customer option').each(function () {
                if (opt.attr('customer_id') == $(this).val())
                priceCustomer = $(this).val();
            });
            $('#price_customer').val(priceCustomer);
            
        } else subject.djc = $(this).val();
        // subject
        if (subject.title && subject.djc) $('#subject').val(subject.title + ' ; Djc-' + subject.djc);
        else if (subject.title) $('#subject').val(subject.title);
    });

    // calculate profit
    const profitState = {sp_total: 0, bp_subtotal: 0, skill_total: 0, bp_total: 0};
    function calcProfit() {
        const {sp_total, bp_total, skill_total} = profitState;
        const profit = sp_total - (bp_total + skill_total);
        let pcent_profit = profit/(bp_total + skill_total) * 100;
        pcent_profit = isFinite(pcent_profit) ? Math.round(pcent_profit) : 0;

        const profitText = bp_total > 0 ? 
            `${accounting.formatNumber(profit)} : ${pcent_profit}%` : accounting.formatNumber(profit);
        $('.profit').text(profitText);

        if (profit < 0) $('.profit').removeClass('text-dark').addClass('text-danger');
        else $('.profit').removeClass('text-danger').addClass('text-dark');

        // budget limit 30 percent
        if (sp_total < bp_total * 1.3) {
            $('.budget-alert').removeClass('d-none');
            // scroll(0, 0);
        } else $('.budget-alert').addClass('d-none');
    }

    /**
     * Table logic
     */
    // add title
    let titleId = 2;
    const titleHtml = $("#titleRow").html();
    $("#titleRow").remove();
    $('#addTitle').click(function() {
        $('#quoteTbl tbody tr.invisible').remove();

        const i = 't'+titleId;
        const newTitleHtml = '<tr>' + titleHtml.replace(/t1/g, i) + '</tr>';
        $("#quoteTbl tbody").append(newTitleHtml);
        titleId++;
        calcTotal();
        adjustTbodyHeight();
    });

    // add product
    let rowId = 1;
    const rowHtml = $("#productRow").html();
    $('#name-p0').autocomplete(autoComp('p0'));
    $('#addProduct').click(function() {
        $('#quoteTbl tbody tr.invisible').remove();

        const i = 'p' + rowId;
        const newRowHtml = '<tr>' + rowHtml.replace(/p0/g, i) + '</tr>';
        $("#quoteTbl tbody").append(newRowHtml);
        $('#name-'+i).autocomplete(autoComp(i));
        rowId++;
        calcTotal();
        // trigger lead change to reset client pricelist 
        $('#lead_id').change();     

        adjustTbodyHeight();
    });
    // adjust tbody height to accomodate dropdown menu
    function adjustTbodyHeight(rowCount) {
        rowCount = rowCount || $('#quoteTbl tbody tr').length;
        if (rowCount < 4) {
            const rows = [];
            for (let i = 0; i < 5; i++) {
                const tr = `<tr class="invisible"><td colspan="100%"></td><tr>`
                rows.push(tr);
            }
            $('#quoteTbl tbody').append(rows.join(''));
        }
    }

    // add miscellaneous product
    $('#addMisc').click(function() {
        $('#quoteTbl tbody tr.invisible').remove();

        const i = 'p' + rowId;
        const newRowHtml = `<tr class="misc"> ${rowHtml.replace(/p0/g, i)} </tr>`;
        $("#quoteTbl tbody").append(newRowHtml);
        $('#name-'+i).autocomplete(autoComp(i));
        $('#misc-'+i).val(1);
        $('#qty-'+i).val(1);
        ['qty', 'rate', 'price', 'amount', 'lineprofit'].forEach(v => {
            $(`#${v}-${i}`).addClass('invisible');
        });
        rowId++;
        calcTotal();
        adjustTbodyHeight();
    });

    // On clicking action drop down
    $("#quoteTbl").on("click", ".up, .down, .delete, .add-title, .add-product, .add-misc", function() {
        const menu = $(this);
        const row = menu.parents("tr:first");
        if (menu.is('.up')) row.insertBefore(row.prev());
        if (menu.is('.down')) row.insertAfter(row.next());
        if (menu.is('.delete') && confirm('Are you sure?')) {
            menu.parents('tr:first').remove();
            $('#quoteTbl tbody tr.invisible').remove();
            adjustTbodyHeight(1);
        }

        // dropdown menus
        if (menu.is('.add-title')) {
            $('#addTitle').click();
            const titleRow = $("#quoteTbl tbody tr:last");
            $("#quoteTbl tbody tr:last").remove();
            row.before(titleRow);
        } else if (menu.is('.add-product')) {
            $('#addProduct').click();
            const productRow = $("#quoteTbl tbody tr:last");
            $("#quoteTbl tbody tr:last").remove();
            row.after(productRow);
        } else if (menu.is('.add-misc')) {
            $('#addMisc').click();
            const miscRow = $("#quoteTbl tbody tr:last");
            $("#quoteTbl tbody tr:last").remove();
            row.after(miscRow);
        }

        calcTotal();
    });

    // on change qty and rate
    $("#quoteTbl").on("change", ".qty, .rate, .buyprice, .estqty", function() {
        const id = $(this).attr('id').split('-')[1];

        const qty = accounting.unformat($('#qty-'+id).val());
        let buyprice = accounting.unformat($('#buyprice-'+id).val());
        let estqty = accounting.unformat($('#estqty-'+id).val() || '1');
        let rate = accounting.unformat($('#rate-'+id).val());

        // row item % profit
        let price = rate * ($('#tax_id').val()/100 + 1);
        let profit = (qty * rate) - (estqty * buyprice);
        let pcent_profit = profit / (estqty * buyprice) * 100;
        pcent_profit = isFinite(pcent_profit)? Math.round(pcent_profit) : 0;

        $('#buyprice-'+id).val(accounting.formatNumber(buyprice));
        $('#rate-'+id).val(accounting.formatNumber(rate));
        $('#price-'+id).val(accounting.formatNumber(price));
        $('#amount-'+id).text(accounting.formatNumber(qty * price));
        $('#lineprofit-'+id).text(pcent_profit + '%');
        calcTotal();
    });

    // on tax change
    $('#tax_id').change(function() {
        const tax = $(this).val()/100 + 1;
        $('#quoteTbl tbody tr').each(function() {
            const qty = $(this).find('.qty').val() * 1;
            if (qty > 0) {
                const rate = accounting.unformat($(this).find('.rate').val());
                let price = rate * tax;
                $(this).find('.price').val(accounting.formatNumber(price));
                $(this).find('.rate').change();
            }
        });
    });    

    // compute totals
    function calcTotal() {
        let total = 0;
        let subtotal = 0;
        let bp_subtotal = 0;
        $("#quoteTbl tbody tr").each(function(i) {
            const isMisc = $(this).hasClass('misc');
            const qty = $(this).find('.qty').val() * 1;
            if (qty > 0) {
                if (!isMisc) {
                    const amount = accounting.unformat($(this).find('.amount').text());
                    const rate = accounting.unformat($(this).find('.rate').val());
                    total += amount * 1;
                    subtotal += qty * rate;
                }
                // profit variables
                const buyprice = accounting.unformat($(this).find('.buyprice').val());
                const estqty = $(this).find('.estqty').val();
                bp_subtotal += estqty * buyprice;
            }
            $(this).find('.index').val(i);
        });
        $('#total').val(accounting.formatNumber(total));
        $('#subtotal').val(accounting.formatNumber(subtotal));
        $('#tax').val(accounting.formatNumber((total - subtotal)));
        profitState.bp_total = bp_subtotal;
        profitState.sp_total = subtotal;
        calcProfit();        
    }


    /**
     * Skillset modal logic
     */
    // remove skill row
    $('#skillTbl').on('click', '.rem', function() {
        $(this).parents('tr').remove();
        skillTotal();
    });
    $('#skillTbl').on('change', '.type, .chrg, .hrs, .tech', function() {
        const row = $(this).parents('tr');
        let hrs = row.find('.hrs').val();
        let tech = row.find('.tech').val();
        let chrg = row.find('.chrg');

        // labour type charges
        switch (row.find('.type').val()) {
            case 'casual': chrg.val(250).attr('readonly', true); break;
            case 'contract': chrg.val(250).attr('readonly', true); break;
            case 'attachee': chrg.val(150).attr('readonly', true); break;
            case 'outsourced': chrg.val(chrg.val()).attr('readonly', false); break;
        }
        skillTotal();
    });

    // add skill row
    let skillId = 0;
    const skillHtml = $('#skillTbl tbody tr:first').html();
    $('#skillTbl tbody tr:first').remove();
    $('#addRow').click(function() {
        skillId++;
        const html = skillHtml.replace(/-0/g, '-'+skillId).replace('d-none', '');
        $('#skillTbl tbody').append('<tr>'+html+'</tr>');
    });

    function skillTotal() {
        total = 0;
        $('#skillTbl tbody tr').each(function() {
            const hrs = $(this).find('.hrs').val();
            const tech = $(this).find('.tech').val();
            const chrg = $(this).find('.chrg').val();
            const amount = hrs * chrg * tech;
            total += amount;
            $(this).find('.amount').text(amount);
        });
        $('#skill_total').val(accounting.formatNumber(total));
        profitState.skill_total = total;
        calcProfit();
    }

    // autocomplete function
    function autoComp(i) {
        return {
            source: function(request, response) {
                // stock product
                let term = request.term;
                let url = "{{ route('biller.products.quote_product_search') }}";
                let data = {
                    keyword: term, 
                    price_customer_id: $('#price_customer').val(),
                };
                // maintenance service product 
                const docType = @json(request('doc_type'));
                if (docType == 'maintenance') {
                    url = "{{ route('biller.taskschedules.quote_product_search') }}";
                    data.customer_id = $('#lead_id option:selected').attr('customer_id');
                } 
                $.ajax({
                    url, data,
                    method: 'POST',
                    success: result => response(result.map(v => ({label: v.name, value: v.name, data: v}))),
                });
            },
            autoFocus: true,
            minLength: 0,
            select: function(event, ui) {
                const {data} = ui.item;

                $('#productid-'+i).val(data.id);
                $('#name-'+i).val(data.name);
                $('#unit-'+i).val(data.unit);                
                $('#qty-'+i).val(1);                
                $('#buyprice-'+i).val(accounting.formatNumber(data.purchase_price)); 
                $('#estqty-'+i).val(1);

                const rate = parseFloat(data.price);
                let price = rate * ($('#tax_id').val()/100 + 1);
                $('#price-'+i).val(accounting.formatNumber(price));                
                $('#amount-'+i).text(accounting.formatNumber(price));
                $('#rate-'+i).val(accounting.formatNumber(rate)).change();

                if (data.units) {
                    let units = data.units.filter(v => v.unit_type == 'base');
                    if (units.length) $('#unit-'+i).val(units[0].code);
                }
            }
        };
    }    
</script>
@endsection