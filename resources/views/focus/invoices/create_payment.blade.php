@extends ('core.layouts.app')

@section('title', 'Invoice Payment | Create')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h4 class="content-header-title">Invoice Payment Management</h4>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::open(['route' => 'biller.invoices.store_payment', 'method' => 'POST', 'id' => 'stoPaymnt']) }}
                        <div class="row mb-1">
                            <div class="col-4">
                                <label for="date" class="caption">Date</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                    {{ Form::date('date', null, ['class' => 'form-control round', 'id' => 'date', 'required']) }}
                                </div>
                            </div>
                            <div class="col-4">
                                <label for="reference" class="caption">Reference</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                    {{ Form::text('tid', 1, ['class' => 'form-control round', 'id' => 'tid', 'disabled']) }}
                                    <input type="hidden" name="tid">
                                </div>
                            </div>
                            <div class="col-4">
                                <label for="amount" class="caption">Amount</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                    {{ Form::number('amount', null, ['class' => 'form-control round', 'step' => 2, 'id' => 'amount', 'required']) }}
                                </div>
                            </div>
                        </div> 

                        <div class="row mb-1">
                            <div class="col-4">
                                <label for="ledgerAccount" class="caption">Ledger Account</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                    <select class="form-control" name="account" id="account"></select>
                                </div>
                            </div>
                            <div class="col-4">
                                <label for="customer" class="caption">Search Customer</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                    <select id="person" name="customer_id" class="form-control select-box" required>
                                        <option value="">-- Select Customer --</option>
                                    </select>
                                </div>
                            </div>                            
                        </div>

                        <table class="table-responsive tfr my_stripe_single">
                            <thead>
                                <tr class="item_header bg-gradient-directional-blue white">
                                    <th width="10%" class="text-center">#</th>
                                    <th width="10%">Invoice No.</th>
                                    <th width="40%" class="text-center">Description</th>
                                    <th width="20%" class="text-center">Amount (VAT Inc)</th>
                                    <th width="20%" class="text-center">Payment</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center">1</td>
                                    <td>Inv-0874</td>
                                    <td>Repair of Faulty 24000Btu Hi wall Garissa Branch ; Djc-4595</td>
                                    <td class="text-center">12,500</td>
                                    <td><input type="text" class="form-control" value="{{ '10,500' }}"></td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="row">                            
                            <div class="col-3 ml-auto"> 
                                <label for="total" class="caption font-weight-bold">Total</label>                               
                                <div class="input-group">                                    
                                    {{ Form::text('total', '10,500', ['class' => 'form-control', 'id' => 'total', 'readonly']) }}
                                </div> 
                                {{ Form::submit('Create Payment', ['class' => 'btn btn-primary btn-lg mt-1', 'disabled']) }}                          
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

    // initialize customer select2
    $('#person').select2({
        ajax: {
            url: "{{ route('biller.customers.select') }}",
            dataType: 'json',
            type: 'POST',
            quietMillis: 50,
            data: function(params) { 
                return { search: params.term }
            },
            processResults: function(data) {
                return {
                    results: $.map(data, function(item) {
                        return {
                            text: `${item.name} - ${item.company}`,
                            id: item.id
                        }
                    })
                };
            },
        }
    });

</script>
@endsection
