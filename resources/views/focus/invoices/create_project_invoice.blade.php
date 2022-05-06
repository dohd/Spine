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
        let total = 0;
        let subtotal = 0; 
        $('#quoteTbl tbody tr').each(function(i) {
            if ($('#quoteTbl tbody tr:last').index() == i) return;
            const subtStr = $('#initprice-'+i).val().replace(/,/g, '');
            const rateExc = parseFloat(subtStr);
            subtotal += rateExc;
            total += rateExc * ($('#tax_id').val() / 100 + 1);
            $(this).find('.rate').val(rateExc.toLocaleString());
            $(this).find('.amount').text(rateExc.toLocaleString());
        });
        $('#subtotal').val(parseFloat(subtotal.toFixed(2)).toLocaleString());
        $('#total').val(parseFloat(total.toFixed(2)).toLocaleString());
        const tax = (total - subtotal).toFixed(2);
        $('#tax').val(parseFloat(tax).toLocaleString());
    });
    $('#tax_id').trigger('change');
</script>
@endsection