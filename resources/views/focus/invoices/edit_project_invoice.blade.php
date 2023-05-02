@extends ('core.layouts.app')

@section('title', 'Edit Project Invoice')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="col-12">
            <div class="btn-group float-right">
                @include('focus.invoices.partials.invoices-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::model($invoice, ['route' => ['biller.invoices.update_project_invoice', $invoice], 'method' => 'POST']) }}
                        @php $customer = $invoice->customer; @endphp
                        @include('focus.invoices.project_invoice_form')
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
{{ Html::script('core/app-assets/vendors/js/extensions/sweetalert.min.js') }}
<script type="text/javascript">
    const config = {
        date: {format: "{{config('core.user_date_format')}}", autoHide: true},
    };    

    const invoice = @json($invoice);
    // Initialize datepicker
    $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
    if (invoice.invoicedate) $('#invoicedate').datepicker('setDate', new Date(invoice.invoicedate));
    
    // on tax change
    $('#tax_id').change(function() {
        // check if row items are of the same tax bracket
        const rowTaxeRates = [];
        $('#quoteTbl tbody tr').each(function(i) {
            rowTaxeRates.push($(this).find('.taxrate').val()*1);
        });
        const mainTax = $('#tax_id').val()*1;
        if (!rowTaxeRates.includes(mainTax) && mainTax != 0) {
            $('#tax_id').val('');
            return alert('Cannot apply a different Tax Rate from the original!');
        }
        
        // compute totals
        let taxable = 0;
        let subtotal = 0; 
        let total = 0;
        $('#quoteTbl tbody tr').each(function(i) {
            $(this).find('.row-index').val(i);
            const qty = accounting.unformat($(this).find('.qty').val());
            const rowSubtotal = accounting.unformat($(this).find('.rate').val());
            const rowTaxRate = accounting.unformat($(this).find('.taxrate').val());
            let mainTaxRate = $('#tax_id').val();

            if (rowTaxRate == 0) mainTaxRate = 0;
            rowTax = rowSubtotal * mainTaxRate / 100;
            if (rowTaxRate > 0) taxable += qty * rowSubtotal;
            subtotal += qty * rowSubtotal;
            price = rowSubtotal + rowTax;
            total += qty * price;
            
            $(this).find('.price').val(accounting.formatNumber(price, 4));
            $(this).find('.amount').text(accounting.formatNumber(qty * price, 4));
        });
        
        let tax = total - subtotal
        $('#tax').val(accounting.formatNumber(tax));
        $('#taxable').val(accounting.formatNumber(taxable));
        $('#subtotal').val(accounting.formatNumber(subtotal));
        $('#total').val(accounting.formatNumber(total));
    });

    // load invoice items
    const invoiceItemRow = $('#quoteTbl tbody tr:first').html();
    $('#quoteTbl tbody tr').remove();
    const invoiceItems = @json(@$invoice->products()->with('quote')->get());
    if (invoiceItems) {
        invoiceItems.forEach(v => {
            const prefixes = @json(prefixesArray(['quote', 'proforma_invoice'], $invoice->ins));
            // update initial invoice items that lacked product_subtotal value
            if (v.product_price*1 > 0 && v.product_subtotal*1 == 0) {
                v.tax_rate = invoice.tax_id;
                v.product_subtotal = v.product_price;
                v.product_price = v.product_subtotal * (1 + v.tax_rate/100);
            } 
            console.log(v)
            $('#quoteTbl tbody').append(`<tr>${invoiceItemRow}</tr>`);
            const row = $('#quoteTbl tbody tr:last');

            row.find('.num').text(v.numbering);
            row.find('.num-val').val(v.numbering);
            row.find('.row-indx').val(v.row_index);

            const quote = v.quote;
            if (quote) {
                const tid = `${quote.tid}`.length < 4? `000${quote.tid}`.slice(-4) : quote.tid;
                const pfx = quote.bank_id == 0? prefixes[0] : prefixes[1];
                row.find('.ref').val(`${pfx}-${tid}`);
                row.find('.quote-id').val(quote.id);
                row.find('.branch-id').val(quote.branch_id);
                const project_id = quote.project_quote? quote.project_quote.project_id : '';
                row.find('.project-id').val(project_id);
            }

            row.find('.descr').val(v.description);
            row.find('.unit').val(v.unit);
            row.find('.taxrate').val(v.tax_rate*1);
            row.find('.qty').val(v.product_qty*1);
            row.find('.rate').val(accounting.formatNumber(v.product_subtotal*1, 4));
            row.find('.price').val(accounting.formatNumber(v.product_price*1, 4));

            const amount = v.product_qty * v.product_subtotal * (1 + v.tax_rate/100);
            row.find('.amount').text(accounting.formatNumber(amount, 4));
        });
        $('#tax_id').change();
    }

    // remove reference column from standard invoice type
    const isStandard = @json(@$invoice->is_standard);
    if (isStandard) {
        $('#quoteTbl thead tr:first th:eq(1)').remove();
        $('#quoteTbl tbody tr').each(function() {
            $(this).find('td:eq(1)').remove();
        });
    }
</script>
@endsection