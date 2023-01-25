@extends ('core.layouts.app')

@section('title', 'Create Project Invoice')

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
    .datepicker('setDate', new Date())

    // On selecting Tax
    $('#tax_id').change(function() {
        let tax = 0;
        let subtotal = 0; 
        let total = 0;
        $('#quoteTbl tbody tr').each(function(i) {
            let lineSubtotal = accounting.unformat($(this).find('.subtotal').val());
            let lineQty = accounting.unformat($(this).find('.qty').val());
            let lineTotal = lineSubtotal * lineQty;
            const taxRate = $('#tax_id').val() / 100;
           
            tax += lineTotal * taxRate;
            subtotal += lineTotal;
            total += lineTotal * (1+taxRate);
             
            $(this).find('.rate').val(accounting.formatNumber(lineSubtotal));
            $(this).find('.amount').text(accounting.formatNumber(lineTotal));
        });
        $('#subtotal').val(accounting.formatNumber(subtotal));
        $('#tax').val(accounting.formatNumber(tax));
        $('#total').val(accounting.formatNumber(total));
    });
    $('#tax_id').change();

    
    /**
     * Dynamic Invoice Type
    */
    const invoiceItemRow = $('#quoteTbl tbody tr:first').html();
    const quote = @json(@$quotes->first());
    const prefixes = @json($prefixes);
    $('#invoice_type').change(function() {
        $('#quoteTbl tbody').html('');
        if (this.value == 'collective') {
            $('#quoteTbl tbody').append(`<tr>${invoiceItemRow}</tr>`);
        } else {
            if (quote && quote.verified_products) {
                const items = quote.verified_products;
                items.forEach((v,i) => {
                    $('#quoteTbl tbody').append(`<tr>${invoiceItemRow}</tr>`);
                    const row = $('#quoteTbl tbody tr:last');

                    row.find('.num').text(v.numbering);
                    row.find('.num-val').val(v.numbering);
                    row.find('.row-index').val(v.row_index);

                    const tid = `${quote.tid}`.length < 4? `000${quote.tid}`.slice(-4) : quote.tid;
                    const pfx = quote.bank_id == 0? prefixes[1] : prefixes[2];
                    row.find('.ref').val(`${pfx}-${tid}`);

                    row.find('.descr').val(v.product_name);
                    row.find('.unit').val(v.unit);

                    const qty = parseFloat(v.product_qty);
                    row.find('.qty').val(qty);

                    const price = parseFloat(v.product_subtotal);
                    row.find('.subtotal').val(accounting.formatNumber(price));
                    row.find('.rate').val(accounting.formatNumber(price));
                    row.find('.amount').text(accounting.formatNumber(qty * price));

                    row.find('.quote-id').val(quote.id);
                    row.find('.branch-id').val(quote.branch_id);

                    const project_id = quote.project_quote? quote.project_quote.project_id : '';
                    row.find('.project-id').val(project_id);
                });
            }
        }
    }).trigger('change');   
    
</script>
@endsection