@extends ('core.layouts.app')

@section('title', 'Create | Invoice Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Invoice Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.invoices.partials.invoices-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::open(['route' => 'biller.invoices.store_project_invoice', 'method' => 'POST']) }}
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
    // Initialize datepicker
    $('.datepicker')
    .datepicker({format: "{{config('core.user_date_format')}}", autoHide: true})
    .datepicker('setDate', new Date());

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
            const rowTaxable = accounting.unformat($(this).find('.taxable').val());
            if ($('#invoice_type').val() == 'standard') {
                const mainTaxRate = $('#tax_id').val() / 100;
                const rowTaxRate = $(this).find('.taxrate').val() / 100;
                if (mainTaxRate != rowTaxRate) rowTax = rowTaxable * rowTaxRate;
                else rowTax = rowTaxable * mainTaxRate;

                if (rowTax > 0) {
                    taxable += qty * rowTaxable;
                    if (mainTaxRate == 0) rowTax = 0;
                }
                subtotal += qty * rowSubtotal;
                price = rowSubtotal + rowTax;
                total += qty * price;

                $(this).find('.price').val(accounting.formatNumber(price, 4));
                $(this).find('.amount').text(accounting.formatNumber(qty * price, 4));
            } else {
                rowTax = rowTaxable * $('#tax_id').val() / 100;
                taxable += qty * rowTaxable;
                subtotal += qty * rowSubtotal;
                price = rowSubtotal + rowTax;
                total += qty * price;
                $(this).find('.price').val(accounting.formatNumber(price, 4));
                $(this).find('.amount').text(accounting.formatNumber(qty * price, 4));
            }
        });
        
        let tax = total - subtotal
        if ($('#invoice_type').val() == 'standard') 
            tax = taxable * $('#tax_id').val() / 100;
        $('#tax').val(accounting.formatNumber(tax));
        $('#taxable').val(accounting.formatNumber(taxable));
        $('#subtotal').val(accounting.formatNumber(subtotal));
        $('#total').val(accounting.formatNumber(total));
    });

    // multiple collective line items
    if (!$('#invoice_type').length && $('#quoteTbl tbody tr').length) {
        $('#tax_id').change();
    }

    /**
     * Dynamic Invoice Type
    */
    const invoiceItemRow = $('#quoteTbl tbody tr:first').html();
    $('#invoice_type').change(function() {
        $('#quoteTbl tbody tr').remove();
        ['taxable', 'subtotal', 'tax', 'total'].forEach(v => {
            $(`#${v}`).val('');
        });
        
        // append line items
        if (this.value == 'collective') {
            $('#quoteTbl tbody').append(`<tr>${invoiceItemRow}</tr>`);
        } else {
            const quote = @json(@$quotes->first());
            const prefixes = @json(@$prefixes);
            if (quote && quote.verified_products) {
                const items = quote.verified_products;
                items.forEach((v,i) => {
                    $('#quoteTbl tbody').append(`<tr>${invoiceItemRow}</tr>`);
                    const row = $('#quoteTbl tbody tr:last');
                    
                    row.find('.num').text(v.numbering);
                    row.find('.num-val').val(v.numbering);
                    row.find('.row-index').val(v.row_index);                    
                    row.find('.verification-id').text(v.verification_id);

                    const tid = `${quote.tid}`.length < 4? `000${quote.tid}`.slice(-4) : quote.tid;
                    const pfx = quote.bank_id == 0? prefixes[1] : prefixes[2];
                    row.find('.ref').val(`${pfx}-${tid}`);

                    row.find('.descr').val(v.product_name);
                    row.find('.unit').val(v.unit);

                    row.find('.taxrate').val(v.tax_rate*1);
                    row.find('.taxable').val(accounting.formatNumber(v.product_subtotal*1, 4));

                    row.find('.qty').val(v.product_qty*1);
                    row.find('.rate').val(accounting.formatNumber(v.product_subtotal*1, 4));
                    row.find('.price').val(accounting.formatNumber(v.product_price*1, 4));

                    const amount = v.product_qty * v.product_subtotal * (1 + v.tax_rate/100);
                    row.find('.amount').text(accounting.formatNumber(amount, 4));

                    row.find('.quote-id').val(quote.id);
                    row.find('.branch-id').val(quote.branch_id);
                    const project_id = quote.project_quote? quote.project_quote.project_id : '';
                    row.find('.project-id').val(project_id);
                });
                $('#tax_id').change();
            }
        }
        // apply tax
        $('#tax_id').change();
    }).trigger('change');
</script>
@endsection