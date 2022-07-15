@extends ('core.layouts.app')

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
            <strong>Profit Margin Not Met!</strong> Check line item rates.
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
            @if ($task)
                {{ Form::model($quote, ['route' => ['biller.quotes.store', $quote], 'method' => 'post']) }}
            @else
                {{ Form::model($quote, ['route' => ['biller.quotes.update', $quote], 'method' => 'patch']) }}
            @endif
                @include('focus.quotes.form')
            {{ Form::close() }}
        </div>
    </div> 
</div>
@endsection

@section('extra-scripts')
<script>   
    $.ajaxSetup({headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }});

    // default edit values
    $('#branch_id').val("{{ $quote->branch_id }}");
    $('#customer_id').val("{{ $quote->customer_id }}");
    const printType = "{{ $quote->print_type }}"
    if (printType == 'inclusive') {
        $('#colorCheck7').attr('checked', false);
        $('#colorCheck6').attr('checked', true);
    }

    // initialize datepicker
    $('.datepicker')
    .datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    $('#referencedate').datepicker('setDate', new Date());
    $('#date').datepicker('setDate', new Date());

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
        if (subject.title && subject.djc) $('#subject').val(subject.title + ' ; Djc-' + subject.djc);
        else if (subject.title) $('#subject').val(subject.title);
    });

    // calculate profit
    const profitState = {sp_ttl: 0, bp_subttl: 0, skill_ttl: 0};
    function calcProfit() {
        const {sp_ttl, bp_ttl, skill_ttl} = profitState;
        const profit = parseFloat((sp_ttl - bp_ttl - skill_ttl).toFixed(2));
        const pcent = Math.round(profit/bp_ttl * 100);

        const profitText = bp_ttl > 0 ?  profit.toLocaleString() + ' : ' + pcent + '%' : profit.toLocaleString();
        $('.profit').text(profitText);

        if (profit < 0) $('.profit').removeClass('text-dark').addClass('text-danger');
        else $('.profit').removeClass('text-danger').addClass('text-dark');

        // budget limit 30 percent
        if (sp_ttl < bp_ttl * 1.3) {
            $('.budget-alert').removeClass('d-none');
            scroll(0, 0);
        } else $('.budget-alert').addClass('d-none');
    }


    /**
     * Table logic
     */
    // default autocomplete
    $("#quoteTbl tbody tr").each(function() {
        const id = $(this).find('.pname').attr('id');
        if (id) {
            const i = id.split('-')[1];
            $('#name-'+i).autocomplete(autoComp(i));
        }
    })

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
    // add product
    const rowHtml = $("#productRow").html();
    $("#productRow").remove();
    let rowId = $("#quoteTbl tbody tr").length;
    $('#addProduct').click(function() {
        const i = 'p' + rowId;
        const newRowHtml = '<tr>' + rowHtml.replace(/p0/g, i) + '</tr>';
        $("#quoteTbl tbody").append(newRowHtml);
        $('#name-'+i).autocomplete(autoComp(i));
        rowId++;
        calcTotal();
    });

    // add title
    const titleHtml = $("#titleRow").html();
    $("#titleRow").remove();
    let titleId = $("#quoteTbl tbody tr").length;
    $('#addTitle').click(function() {
        const i = 't'+titleId;
        const newTitleHtml = '<tr>' + titleHtml.replace(/t1/g, i) + '</tr>';
        $("#quoteTbl tbody").append(newTitleHtml);
        titleId++;
        calcTotal();
    });

    // on change qty and rate
    $("#quoteTbl").on("change", ".qty, .rate, .buyprice, .estqty", function() {
        const id = $(this).attr('id').split('-')[1];
        const qty = $('#qty-'+id).val() || '0';
        let rate = $('#rate-'+id).val() || '0';
        let price = rate.replace(/,/g, '') * ($('#tax_id').val()/100 + 1);
        const amount = parseFloat((qty * price).toFixed(2)).toLocaleString();

        rate = rate.replace(/,/g, '') * 1;
        rate = parseFloat(rate.toFixed(2)).toLocaleString();
        price = parseFloat(price.toFixed(2)).toLocaleString();

        $('#rate-'+id).val(rate);
        $('#price-'+id).val(price);
        $('#amount-'+id).text(amount);

        if (!qty) $('#qty-'+id).val(1);
        if (!$('#buyprice-'+id).val()) $('#buyprice-'+id).val(0);
        if (!$('#estqty-'+id).val()) $('#estqty-'+id).val(1);
        calcTotal();
    });

    // on tax change
    let initTaxChange = 0;
    $('#tax_id').change(function() {
        initTaxChange++;
        $('#quoteTbl tbody tr').each(function() {
            const qty = $(this).find('.qty').val() * 1;
            if (qty > 0) {
                const rate = $(this).find('.rate').val().replace(/,/g, '');
                let price = rate * ($('#tax_id').val()/100 + 1);
                price = parseFloat(price.toFixed(2)).toLocaleString();

                if (initTaxChange > 1) $(this).find('.price').val(price);                
                $(this).find('.rate').change();
            }
        });
    });       
    $('#tax_id').change();

    // compute totals
    function calcTotal() {
        let grandttl = 0;
        let subttl = 0;
        let bp_subttl = 0;
        $("#quoteTbl tbody tr").each(function(i) {
            $(this).find('.index').val(i);
            const qty = $(this).find('.qty').val() * 1;
            if (qty > 0) {
                const amount = $(this).find('.amount').text().replace(/,/g, '');
                const rate = $(this).find('.rate').val().replace(/,/g, '');
                grandttl += amount * 1;
                subttl += qty * rate;
                
                const buyprice = $(this).find('.buyprice').val().replace(/,/g, '');
                const estqty = $(this).find('.estqty').val();
                bp_subttl += estqty * buyprice;
            }
        });
        $('#total').val(parseFloat(grandttl.toFixed(2)).toLocaleString());
        $('#subtotal').val(parseFloat(subttl.toFixed(2)).toLocaleString());
        $('#tax').val(parseFloat((grandttl - subttl).toFixed(2)).toLocaleString());
        profitState.bp_ttl = bp_subttl;
        profitState.sp_ttl = subttl;
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
                const buyprice = data.purchase_price.replace(/,/g, '')*1;
                $('#buyprice-'+i).val(parseFloat(buyprice.toFixed(2)).toLocaleString()); 

                const rate = data.price.replace(/,/g, '')*1;
                $('#rate-'+i).val(parseFloat(rate.toFixed(2)).toLocaleString());

                let price = rate * ($('#tax_id').val()/100 + 1);
                price = parseFloat(price.toFixed(2)).toLocaleString();
                $('#price-'+i).val(price);                
                $('#amount-'+i).text(price);
                $('#rate-'+i).change();
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
        const hrs = row.find('.hrs').val();
        const tech = row.find('.tech').val();
        const chrg = row.find('.chrg');
        switch (row.find('.type').val()) {
            case 'casual': chrg.val(200).attr('readonly', true); break;
            case 'contract': chrg.val(350).attr('readonly', true); break;
            case 'outsourced': chrg.val(chrg.val()).attr('readonly', false); break;
        }
        skillTotal();
    });

    // add skill row
    let skillId = $('#skillTbl tbody tr').length;
    const skillHtml = $('#skillTbl tbody tr:first').html();
    $('#skillTbl tbody tr:first').remove();
    $('#addRow').click(function() {
        skillId++;
        const html = skillHtml.replace(/-0/g, '-'+skillId);
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
        $('#skill_ttl').val(total.toLocaleString());
        profitState.skill_ttl = total;
        calcProfit();
    }
    skillTotal();
</script>
@endsection