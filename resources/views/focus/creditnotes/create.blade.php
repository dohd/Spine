@extends ('core.layouts.app')

@section('title', $is_debit ? 'Debit Notes Management' : 'Credit Notes Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">{{ $is_debit ? 'Debit Notes Management' : 'Credit Notes Management' }}</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.creditnotes.partials.creditnotes-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::open(['route' => 'biller.creditnotes.store', 'method' => 'POST']) }}
                        @include('focus.creditnotes.form')
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script>
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

    $('.datepicker').datepicker({format: "{{config('core.user_date_format')}}", autoHide: true})
    .datepicker('setDate', new Date())
    
    // Load customers
    $('#customer').select2({
        ajax: {
            url: "{{ route('biller.customers.select') }}",
            dataType: 'json',
            type: 'POST',
            quietMillis: 50,
            data: ({term}) => ({search: term}),
            processResults: result => {
                return { results: result.map(v => ({text: `${v.name} - ${v.company}`, id: v.id }))};
            }      
        }
    });

    // load cutomer invoices
    $('#customer').change(function() {
        $.ajax({
            url: "{{ route('biller.invoices.client_invoices') }}?customer_id=" + $(this).val(),
            success: result => {
                $('#invoice option:not(:eq(0))').remove();
                result.forEach((v, i) => {
                    const txt = 'Inv-' + v.tid + ' ' + v.notes;
                    $('#invoice').append(new Option(txt, v.id));
                });
            }
        });
    });

    // On amount change
    $('#amount').change(function() {
        const amount = $(this).val().replace(/,/g, '') * 1;
        $(this).val(parseFloat(amount.toFixed(2)).toLocaleString());
        calcTotals();
    });
    // On Tax change
    $('#tax_id').change(function() {
        calcTotals();
    });
    // on VAT on Amount Change
    $('#is_tax_exc').change(function() {
        calcTotals();
    });

    function calcTotals() {
        const amount = $('#amount').val().replace(/,/g, '') * 1;
        const tax = $('#tax_id').val()/100;
        const isTaxExc = $('#is_tax_exc').val();
        let subtotal = 0;
        let total = 0;
        if (isTaxExc == 1) {
            subtotal = amount;
            total = amount * (1 + tax);
        } else {
            subtotal = amount / (1 + tax);
            total = amount;
        }
        $('#subtotal').val(parseFloat(subtotal.toFixed(2)).toLocaleString());
        $('#total').val(parseFloat(total.toFixed(2)).toLocaleString());
        $('#tax').val(parseFloat((total - subtotal).toFixed(2)).toLocaleString());
    }
</script>
@endsection