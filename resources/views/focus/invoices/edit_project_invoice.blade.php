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
    // default values
    const invoice = @json($invoice);

    // Initialize datepicker
    $('.datepicker').datepicker({format: "{{config('core.user_date_format')}}", autoHide: true})
    .datepicker('setDate', new Date());
    if (invoice.invoicedate) 
        $('#invoicedate').datepicker('setDate', new Date(invoice.invoicedate));

    // On selecting Tax
    $('#tax_id').change(function() {
        let tax = 0;
        let subtotal = 0; 
        let total = 0;
        $('#quoteTbl tbody tr').each(function(i) {
            let lineSubtotal = accounting.unformat($(this).find('.subtotal').val());
            let lineQty = parseFloat($(this).find('.qty').val());
            const taxRate = $('#tax_id').val() / 100;
            tax += lineSubtotal * taxRate;
            subtotal += lineSubtotal * lineQty;
            total += lineSubtotal * lineQty * (1+taxRate);
            $(this).find('.rate').val(accounting.formatNumber(lineSubtotal));
            $(this).find('.amount').text(accounting.formatNumber(lineSubtotal));
        });
        $('#subtotal').val(accounting.formatNumber(subtotal));
        $('#tax').val(accounting.formatNumber(tax));
        $('#total').val(accounting.formatNumber(total));
    });

    $('#tax_id').change();
</script>
@endsection