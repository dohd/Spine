@extends ('core.layouts.app')

@section('title', 'KRA | Bills Payment Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">KRA Bill</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.bills.partials.bills-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::open(['route' => 'biller.bills.store_kra', 'method' => 'POST']) }}
                        <div class="form-group row">
                            <div class="col-4">
                                <label for="supplier">KRA Creditor</label>
                                <select name="supplier_id" class="form-control"  data-placeholder="Search KRA Creditor" id="supplier" required>
                                </select>
                            </div>
                            <div class="col-2">
                                <label for="tid">Transaction ID</label>
                                {{ Form::text('tid', $tid+1, ['class' => 'form-control', 'readonly']) }}
                            </div>
                            <div class="col-2">
                                <label for="date">Registration Date</label>
                                {{ Form::text('reg_date', null, ['class' => 'form-control datepicker']) }}
                            </div>
                            <div class="col-3">
                                <label for="number">Registration No.</label>
                                {{ Form::text('reg_no', null, ['class' => 'form-control', 'required']) }}
                            </div>                            
                        </div>
                        <div class="form-group row">
                            <div class="col-3">
                                <label for="payment_type">Payment Type</label>
                                {{ Form::text('payment_type', null, ['class' => 'form-control', 'required']) }}
                            </div>   
                            <div class="col-3">
                                <label for="tax_type">Tax Obligation</label>
                                {{ Form::text('tax_type', null, ['class' => 'form-control', 'required']) }}
                            </div>   
                            <div class="col-2">
                                <label for="period">Tax Period</label>
                                {{ Form::text('tax_period', null, ['class' => 'form-control', 'required']) }}
                            </div>
                            <div class="col-2">
                                <label for="amount">Amount (Ksh.)</label>
                                {{ Form::text('amount', null, ['class' => 'form-control', 'id' => 'amount', 'required']) }}
                            </div>                            
                        </div>
                        <div class="form-group row">
                            <div class="col-6">
                                <label for="note">Note</label>
                                {{ Form::text('note', null, ['class' => 'form-control']) }}
                            </div>
                        </div>
                        <div class="form-group row">                            
                            <div class="col-12"> 
                                {{ Form::submit('Generate', ['class' => 'btn btn-primary btn-lg float-right mr-3']) }}                                
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
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

    // datepicker
    $('.datepicker').datepicker({format: "{{config('core.user_date_format')}}", autoHide: true})
    .datepicker('setDate', new Date());

    // Load suppliers
    $('#supplier').select2({
        ajax: {
            url: "{{ route('biller.suppliers.select') }}",
            dataType: 'json',
            type: 'POST',
            quietMillis: 50,
            data: ({term}) => ({keyword: term}),
            processResults: function(data) {
                return {results: data.map(v => ({id: v.id, text: v.name + ' : ' + v.email}))}; 
            },
        }
    });

    // on change maount
    $('#amount').focusout(function() {
        if ($(this).val()) 
            $(this).val(accounting.formatNumber($(this).val(), 2, ','))
    });

</script>
@endsection