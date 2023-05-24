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
                return { results: result.map(v => ({text: v.company, id: v.id }))};
            }      
        }
    });

    // customer invoices
    $('#invoice').select2({
        allowClear: true, 
        ajax: {
            url: "{{ route('biller.invoices.client_invoices') }}",
            dataType: 'json',
            type: 'GET',
            data: ({term}) => ({search: term, customer_id: $("#customer").val()}),
            processResults: data => {
                return { 
                    results: data.map(v => {
                        let tid = v.tid + '';
                        if (tid.length < 4) tid = '0000'.slice(0, 4 - tid.length) + tid;
                        return {text: `Inv-${tid} - ${v.notes}`, id: v.id}
                    })
                }
            },
        }
    });

    // On amount change
    $('#amount').change(function() {
        const amount = accounting.unformat($(this).val());
        this.value = accounting.formatNumber(amount);
        calcTotals();
    });

    // on change tax and vat on amount
    $('form').on('change', '#tax_id, #is_tax_exc', function() {
        calcTotals();
    });

    // compute totals
    function calcTotals() {
        let subtotal = 0;
        let total = 0;
        const amount = accounting.unformat($('#amount').val());
        const tax = $('#tax_id').val()/100;
        if ($('#is_tax_exc').val() == 1) {
            subtotal = amount;
            total = amount * (1 + tax);
        } else {
            subtotal = amount / (1 + tax);
            total = amount;
        }
        $('#subtotal').val(accounting.formatNumber(subtotal));
        $('#total').val(accounting.formatNumber(total));
        $('#tax').val(accounting.formatNumber(total-subtotal));
    }
</script>
@endsection