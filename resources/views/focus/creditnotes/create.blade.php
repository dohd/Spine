@extends ('core.layouts.app')

@section('title', 'Credit Notes Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Credit Notes Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right mr-3">
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
                    <div class="row">
                        <div class="form-group col-3">
                            <label for="customer">Seach Customer</label>
                            <select name="customer_id" id="customer" class="form-control" data-placeholder="Seach Customer" required>
                            </select>
                        </div>
                        <div class="form-group col-3">
                            <label for="invoice">Customer Invoice</label>
                            <select name="invoice_id" id="invoice" class="form-control" required>
                                <option value="">-- Select Customer Invoice --</option>
                            </select>
                        </div>
                        <div class="form-group col-2">
                            <div><label for="tid">Note No.</label></div>
                            {{ Form::text('tid', @$last_cn->tid+1, ['class' => 'form-control', 'readonly']) }}
                        </div>
                        <div class="form-group col-2">
                            <div><label for="date">Date</label></div>
                            {{ Form::text('date', null, ['class' => 'form-control datepicker']) }}
                        </div>
                        <div class="form-group col-2">
                            <label for="tax">Tax</label>
                            <select name="tax_id" id="tax_id" class="form-control round">
                                @foreach ([16, 8, 0] as $val)
                                    <option value="{{ $val }}">
                                        {{ $val ? $val . '% VAT' : 'Off' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-12">
                            <div><label for="note">Note</label></div>
                            {{ Form::text('note', null, ['class' => 'form-control']) }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-2 form-group">
                            <div><label for="amount">Amount</label></div>
                            {{ Form::text('subtotal', null, ['class' => 'form-control', 'id' => 'subtotal']) }}
                        </div>  
                        <div class="col-2 form-group">
                            <div><label for="tax">Tax</label></div>
                            {{ Form::text('tax', null, ['class' => 'form-control', 'id' => 'tax', 'readonly']) }}
                        </div>  
                        <div class="col-2 form-group">
                            <div><label for="total">Total</label></div>
                            {{ Form::text('total', null, ['class' => 'form-control', 'id' => 'total', 'readonly']) }}
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-2 ml-auto">
                            {{ Form::submit('Generate', ['class' => 'btn btn-success block btn-lg']) }}
                        </div>
                    </div>
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