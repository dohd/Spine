@extends ('core.layouts.app')

@section ('title', trans('labels.backend.quotes.management'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="alert alert-warning col-12 d-none budget-alert" role="alert">
            <strong>Profit Margin Not Met!</strong> Check line item rates.
        </div>
        <div class="content-header-left col-md-6 col-12">
            <h4 class="content-header-title">{{ trans('labels.backend.quotes.management') }}</h4>
        </div>
        <div class="content-header-right col-md-6 col-12">
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
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }
    });

    // initialize datepicker
    $('.datepicker')
    .datepicker({ format: "{{ config('core.user_date_format') }}" })
    .change(function() { return $(this).datepicker('hide') });
    $('#referencedate').datepicker('setDate', new Date());
    $('#date').datepicker('setDate', new Date());

    // on selecting lead
    $('#lead_id').change(function() {
        const option = $('#lead_id option:selected');
        $('#subject').val(option.attr('title'));
        $('#client_ref').val(option.attr('client_ref'));
        $('#branch_id').val(option.attr('branch_id'));
        $('#customer_id').val(option.attr('customer_id'));
    });

    // on Djc reference change
    $('#reference').change(function() {
        const title = $('#lead_id option:selected').attr('title');
        const djc = $(this).val();
        if (djc) $('#subject').val(title + ' ; Djc-' + djc);
    });


    /**
     * Table logic
     */
    // 
    function assignIndex() {
        $("#quoteTbl tbody tr").each(function(i) {
            $(this).find('.index').val(i);
        });
    }

    // on clicking action drop down
    $("#quoteTbl").on("click", ".up, .down, .remv", function() {
        var row = $(this).parents("tr:first");
        if ($(this).is('.up')) row.insertBefore(row.prev());
        if ($(this).is('.down')) row.insertAfter(row.next());
        if ($(this).is('.remv')) $(this).closest('tr').remove();
        calcTotal();
        assignIndex();
    });
    // add product
    let rowId = 1;
    const rowHtml = $("#quoteTbl tbody tr:first").html();
    $('#name-p0').autocomplete(autoComp('p0'));
    $('#addProduct').click(function() {
        const i = 'p' + rowId;
        const newRowHtml = '<tr>' + rowHtml.replace(/p0/g, i) + '</tr>';
        $("#quoteTbl tbody").append(newRowHtml);
        $('#name-'+i).autocomplete(autoComp(i));
        assignIndex();
        rowId++;
    });

    // add title
    let titleId = 2;
    const titleHtml = $("#quoteTbl tbody tr:eq(1)").html();
    $("#quoteTbl tbody tr:eq(1)").remove();
    $('#addTitle').click(function() {
        const i = 't'+titleId;
        const newTitleHtml = '<tr>' + titleHtml.replace(/t1/g, i) + '</tr>';
        $("#quoteTbl tbody").append(newTitleHtml);
        assignIndex();
        titleId++;
    });

    // on change qty and rate
    $("#quoteTbl").on("change", ".qty, .rate, .buyprice, .estqty", function() {
        const id = $(this).attr('id').split('-')[1];
        const qty = $('#qty-'+id).val() || '0';
        const rate = $('#rate-'+id).val() || '0';
        const price = rate.replace(/,/g, '') * ($('#tax_id').val()/100 + 1);
        $('#rate-'+id).val(parseFloat(rate.replace(/,/g, '')).toLocaleString());
        $('#price-'+id).val(price.toLocaleString());
        $('#amount-'+id).text((qty * price).toLocaleString());
        if (!qty) $('#qty-'+id).val(1);
        if (!$('#buyprice-'+id).val()) $('#buyprice-'+id).val(0);
        if (!$('#estqty-'+id).val()) $('#estqty-'+id).val(1);
        calcTotal();
    });

    // on tax change
    $('#tax_id').change(function() {
        const tax = $(this).val(); 
        $('#quoteTbl tbody tr').each(function() {
            const qty = $(this).find('.qty').val() * 1;
            if (qty) {
                const rate = $(this).find('.rate').val().replace(/,/g, '');
                const price = rate * (tax/100 + 1);
                $(this).find('.price').val(price.toLocaleString());
                $(this).find('.rate').change();
            }
        });
    });    

    // compute totals
    function calcTotal() {
        let grandttl = 0;
        let subttl = 0;
        let bp_subttl = 0;
        $("#quoteTbl tbody tr").each(function(i) {
            const qty = $(this).find('.qty').val() * 1;
            if (qty > 0) {
                const amount = $(this).find('.amount').text().replace(/,/g, '');
                const rate = $(this).find('.rate').val().replace(/,/g, '');
                const buyprice = $(this).find('.buyprice').val().replace(/,/g, '');
                const estqty = $(this).find('.estqty').val();
                grandttl += amount * 1;
                subttl += qty * rate;
                bp_subttl += estqty * buyprice;
            }
        });
        $('#total').val(parseFloat(grandttl.toFixed(2)).toLocaleString());
        $('#subtotal').val(parseFloat(subttl.toFixed(2)).toLocaleString());
        $('#tax').val(parseFloat((grandttl - subttl).toFixed(2)).toLocaleString());
        // profit
        const profit = parseFloat((subttl - bp_subttl).toFixed(2));
        const pcent = Math.round(profit/bp_subttl * 100);
        $('.profit').text(profit.toLocaleString() + ' : ' + pcent + '%');
        // budget limit 30 percent
        $('.budget-alert').addClass('d-none');
        if (subttl < bp_subttl * 1.3) {
            $('.budget-alert').removeClass('d-none');
            scroll(0, 0);
        }
    }

    // autocomplete function
    function autoComp(i) {
        return {
            source: function(request, response) {
                $.ajax({
                    url: "{{ route('biller.products.quote_product_search') }}",
                    data: 'keyword=' + request.term,
                    success: result => {
                        response(result.map(v => ({label: v.name, value: v.name, data: v})));
                    }
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
                const rate = parseFloat(data.price.replace(/,/g, ''));
                const price = rate * ($('#tax_id').val() / 100 + 1);
                const buyprice = parseFloat(data.purchase_price.replace(/,/g, ''));
                $('#buyprice-'+i).val(buyprice.toLocaleString());                
                $('#price-'+i).val(price.toLocaleString());                
                $('#amount-'+i).text(price.toLocaleString());
                $('#rate-'+i).val(rate.toLocaleString()).change();
            }
        };
    }
</script>
@endsection