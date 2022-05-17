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

    $('.datepicker')
    .datepicker({format: "{{config('core.user_date_format')}}"})
    .datepicker('setDate', new Date())
    .change(function () { $(this).datepicker('hide') });

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
            url: "{{ route('biller.invoices.client_invoices') }}?id=" + $(this).val(),
            success: result => {
                $('#invoice option:not(:eq(0))').remove();
                result.forEach((v, i) => {
                    $('#invoice').append(new Option(v.notes, v.id));
                });
            }
        });
    });

    // On amount change
    $('#subtotal').change(function() {
        const amount = $(this).val().replace(/,/g, '') * 1;
        const total = amount * ($('#tax_id').val() / 100 + 1);
        $(this).val(parseFloat(amount.toFixed(2)).toLocaleString());
        $('#total').val(parseFloat(total.toFixed(2)).toLocaleString());
        $('#tax').val(parseFloat((total-amount).toFixed(2)).toLocaleString());
    });

    // On Tax chnage
    $('#tax_id').change(function() {
        const amount = $('#subtotal').val().replace(/,/g, '') * 1;
        const total = amount * ($(this).val() / 100 + 1);
        $('#subtotal').val(parseFloat(amount.toFixed(2)).toLocaleString());
        $('#total').val(parseFloat(total.toFixed(2)).toLocaleString());
        $('#tax').val(parseFloat((total-amount).toFixed(2)).toLocaleString());
    });
</script>
@endsection