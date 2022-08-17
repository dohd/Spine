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
<script>    
    $.ajaxSetup({headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }});

    // initialize datepicker
    $('.datepicker')
    .datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
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
        } else subject.djc = $(this).val();
        // subject
        if (subject.title && subject.djc) $('#subject').val(subject.title + ' ; Djc-' + subject.djc);
        else if (subject.title) $('#subject').val(subject.title);
    });

    // calculate profit
    const profitState = {sp_total: 0, bp_subtotal: 0, skill_total: 0};
    function calcProfit() {
        const {sp_total, bp_total, skill_total} = profitState;
        const profit = parseFloat((sp_total - bp_total - skill_total).toFixed(2));
        const pcent_profit = Math.round(profit/bp_total * 100);

        const profitText = bp_total > 0 ? 
            `${profit.toLocaleString()} : ${pcent_profit}%` : profit.toLocaleString();
        $('.profit').text(profitText);

        if (profit < 0) $('.profit').removeClass('text-dark').addClass('text-danger');
        else $('.profit').removeClass('text-danger').addClass('text-dark');

        // budget limit 30 percent
        if (sp_total < bp_total * 1.3) {
            $('.budget-alert').removeClass('d-none');
            scroll(0, 0);
        } else $('.budget-alert').addClass('d-none');
    }

    /**
     * Table logic
     */
    // on clicking action drop down
    $("#quoteTbl").on("click", ".up, .down, .remv", function() {
        var row = $(this).parents("tr:first");
        if ($(this).is('.up')) row.insertBefore(row.prev());
        if ($(this).is('.down')) row.insertAfter(row.next());
        if ($(this).is('.remv')) {
            if (confirm('Are you sure?')) $(this).closest('tr').remove();
        }
        calcTotal();
    });

    // add title
    let titleId = 2;
    const titleHtml = $("#titleRow").html();
    $("#titleRow").remove();
    $('#addTitle').click(function() {
        const i = 't'+titleId;
        const newTitleHtml = '<tr>' + titleHtml.replace(/t1/g, i) + '</tr>';
        $("#quoteTbl tbody").append(newTitleHtml);
        titleId++;
        calcTotal();
    });

    // add product
    let rowId = 1;
    const rowHtml = $("#productRow").html();
    $('#name-p0').autocomplete(autoComp('p0'));
    $('#addProduct').click(function() {
        const i = 'p' + rowId;
        const newRowHtml = '<tr>' + rowHtml.replace(/p0/g, i) + '</tr>';
        $("#quoteTbl tbody").append(newRowHtml);
        $('#name-'+i).autocomplete(autoComp(i));
        rowId++;
        calcTotal();
    });

    // add miscellaneous product
    $('#addMisc').click(function() {
        const i = 'p' + rowId;
        const newRowHtml = `<tr class="misc"> ${rowHtml.replace(/p0/g, i)} </tr>`;
        $("#quoteTbl tbody").append(newRowHtml);
        $('#name-'+i).autocomplete(autoComp(i));
        $('#misc-'+i).val(1);
        $('#qty-'+i).val(1).addClass('invisible');
        $('#rate-'+i).addClass('invisible');
        $('#price-'+i).addClass('invisible');
        $('#amount-'+i).addClass('invisible');
        $('#lineprofit-'+i).addClass('invisible');
        rowId++;
        calcTotal();
    });

    // on change qty and rate
    $("#quoteTbl").on("change", ".qty, .rate, .buyprice, .estqty", function() {
        const id = $(this).attr('id').split('-')[1];
        const tax = $('#tax_id').val()/100 + 1;
        const qty = $('#qty-'+id).val() || '0';
        let buyprice = $('#buyprice-'+id).val() || '0';
        let estqty = $('#estqty-'+id).val() || '1';
        let rate = $('#rate-'+id).val() || '0';

        buyprice = buyprice.replace(/,/g, '') * 1;
        estqty = estqty.replace(/,/g, '') * 1;
        rate = rate.replace(/,/g, '') * 1;

        // row item % profit
        let price = rate * tax;
        let profit = (qty * rate) - (estqty * buyprice);
        let pcent_profit = Math.round(profit / (estqty * buyprice) * 100);

        let amount = parseFloat((qty * price).toFixed(2)).toLocaleString();
        rate = parseFloat(rate.toFixed(2)).toLocaleString();
        price = parseFloat(price.toFixed(2)).toLocaleString();
        $('#rate-'+id).val(rate);
        $('#price-'+id).val(price);
        $('#amount-'+id).text(amount);
        $('#lineprofit-'+id).text(pcent_profit + '%');

        calcTotal();
    });

    // on tax change
    $('#tax_id').change(function() {
        const tax = $(this).val()/100 + 1;
        $('#quoteTbl tbody tr').each(function() {
            const qty = $(this).find('.qty').val() * 1;
            if (qty > 0) {
                const rate = $(this).find('.rate').val().replace(/,/g, '');
                let price = rate * tax;

                $(this).find('.price').val(parseFloat(price.toFixed(2)).toLocaleString());
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
                    const amount = $(this).find('.amount').text().replace(/,/g, '');
                    const rate = $(this).find('.rate').val().replace(/,/g, '');
                    total += amount * 1;
                    subtotal += qty * rate;
                }
                // profit variables
                const buyprice = $(this).find('.buyprice').val().replace(/,/g, '');
                const estqty = $(this).find('.estqty').val();
                bp_subtotal += estqty * buyprice;
            }
            $(this).find('.index').val(i);
        });
        $('#total').val(parseFloat(total.toFixed(2)).toLocaleString());
        $('#subtotal').val(parseFloat(subtotal.toFixed(2)).toLocaleString());
        $('#tax').val(parseFloat((total - subtotal).toFixed(2)).toLocaleString());
        profitState.bp_total = bp_subtotal;
        profitState.sp_total = subtotal;
        calcProfit();        
    }

    // autocomplete function
    function autoComp(i) {
        return {
            source: function(request, response) {
                // stock product
                let term = request.term;
                let url = "{{ route('biller.products.quote_product_search') }}";
                let data = {keyword: term, pricegroup_id: $('#pricegroup_id').val()};
                // equipment service product 
                if (term.charAt(0) == '#') {
                    term = term.replace('#', '');
                    url = "{{ route('biller.contractservices.service_product_search') }}";
                    data = {term};
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

                let units = data.units.filter(v => v.unit_type == 'base');
                if (units.length) $('#unit-'+i).val(units[0].code);
            }
        };
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
        $('#skill_total').val(total.toLocaleString());
        profitState.skill_total = total;
        calcProfit();
    }
</script>
@endsection