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
                                <select name="supplier_id" class="form-control"  data-placeholder="Search KRA Creditor" id="supplier"></select>
                            </div>
                            <div class="col-2">
                                <label for="tid">Transaction ID</label>
                                {{ Form::text('tid', 1, ['class' => 'form-control', 'readonly']) }}
                            </div>
                            <div class="col-2">
                                <label for="date">Date</label>
                                {{ Form::text('date', null, ['class' => 'form-control datepicker']) }}
                            </div>
                            <div class="col-2">
                                <label for="due_date">Due Date</label>
                                {{ Form::text('due_date', null, ['class' => 'form-control datepicker']) }}
                            </div>
                            <div class="col-2">
                                <label for="amount">Amount (Ksh.)</label>
                                {{ Form::text('amount', null, ['class' => 'form-control']) }}
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-2">
                                <label for="doc_type">Document Type</label>
                                <select name="doc_type" class="form-control" id="doc_type">
                                    @foreach (['Receipt', 'Invoice', 'DNote', 'Voucher'] as $val)
                                        <option value="{{ $val }}">{{ $val }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-2">
                                <label for="reference">Reference No.</label>
                                {{ Form::text('doc_ref', null, ['class' => 'form-control']) }}
                            </div>
                            <div class="col-8">
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

</script>
@endsection