@extends ('core.layouts.app')

@section('title', 'Bills Payment | Create')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h4 class="content-header-title">Bills Payment</h4>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="media width-250 float-right mr-3">
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
                    {{ Form::open(['route' => 'biller.bills.store', 'method' => 'POST']) }}
                        <div class="row mb-1">
                            <div class="col-5">
                                <label for="payer" class="caption">Search Supplier</label>                                       
                                <select class="form-control" id="supplierbox" data-placeholder="Search Supplier"></select>
                                <input type="hidden" name="supplier_id" value="0" id="supplierid">
                            </div>

                            <div class="col-2">
                                <label for="reference" class="caption">Transaction ID</label>
                                <div class="input-group">
                                    {{ Form::text('tid', 1, ['class' => 'form-control', 'id' => 'tid', 'readonly']) }}
                                </div>
                            </div> 

                            <div class="col-2">
                                <label for="date" class="caption">Date</label>
                                <div class="input-group">
                                    {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required']) }}
                                </div>
                            </div>

                            <div class="col-2">
                                <label for="date" class="caption">Due Date</label>
                                <div class="input-group">
                                    {{ Form::text('due_date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required']) }}
                                </div>
                            </div>                                                                            
                        </div> 

                        <div class="row mb-2">  
                            <div class="col-3">
                                <label for="payment_mode">Payment Mode</label>
                                <select name="payment_mode" id="" class="form-control" required>
                                   <option value="">-- Select Mode --</option>
                                    @foreach (['eft', 'rtgs','cash', 'mpesa', 'cheque'] as $val)
                                        <option value="{{ $val }}">{{ strtoupper($val) }}</option>
                                    @endforeach
                                </select>
                            </div>  
                            <div class="col-2">
                                <label for="deposit" class="caption">Deposit (Ksh.)</label>
                                <div class="input-group">
                                    {{ Form::text('deposit', null, ['class' => 'form-control', 'id' => 'deposit', 'required']) }}
                                </div>
                            </div>  
                            <div class="col-2">
                                <label for="paid_from">Paid From</label>
                                <select name="account_id" id="" class="form-control" required>
                                   <option value="">-- Select Bank --</option>
                                    @foreach ($accounts as $row)
                                        <option value="{{ $row->id }}">{{ $row->holder }}</option>
                                    @endforeach
                                </select>
                            </div>  
                            <div class="col-2">
                                <label for="payment_mode">Document Type</label>
                                <select name="doc_ref_type" id="" class="form-control" required>
                                   <option value="">-- Select Type --</option>
                                    @foreach (['receipt', 'dnote', 'invoice'] as $val)
                                        <option value="{{ $val }}">{{ ucfirst($val) }}</option>
                                    @endforeach
                                </select>
                            </div>     
                            <div class="col-2">
                                <label for="reference" class="caption">Document Reference</label>
                                <div class="input-group">
                                    {{ Form::text('doc_ref', null, ['class' => 'form-control', 'required']) }}
                                </div>
                            </div>                                                     
                        </div>

                        <table class="table-responsive tfr my_stripe_single" id="billsTbl">
                            <thead>
                                <tr class="item_header bg-gradient-directional-blue white">
                                    <th width="15%" class="text-center">Due date</th>
                                    <th width="5%">Bill Number</th>
                                    <th width="40%" class="text-center">Note</th>
                                    <th width="10%">Status</th>
                                    <th width="15%" class="text-center">Amount (VAT Inc)</th>
                                    <th width="15%" class="text-center">Paid (Ksh.)</th>
                                </tr>
                            </thead>
                            <tbody>                                
                                <tr class="bg-white">
                                    <td colspan="4"></td>
                                    <td colspan="2">
                                        <div class="row no-gutters mb-1">
                                            <div class="col-6 pl-3 pt-1"><b>Total Bill:</b></div>
                                            <div class="col-6">
                                                 {{ Form::text('amount_ttl', 0, ['class' => 'form-control', 'id' => 'amount_ttl', 'readonly']) }}
                                            </div>                          
                                        </div>
                                        <div class="row no-gutters">
                                            <div class="col-6 pl-3 pt-1"><b>Total Paid:</b></div>
                                            <div class="col-6">
                                            {{ Form::text('deposit_ttl', 0, ['class' => 'form-control', 'id' => 'deposit_ttl', 'readonly']) }}
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>                
                        </table>

                        <div class="row mt-1">                            
                            <div class="col-12"> 
                                {{ Form::submit('Receive Payment', ['class' => 'btn btn-primary btn-lg float-right']) }}                          
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

    // Load suppliers
    $('#supplierbox').select2({
        ajax: {
            url: "{{ route('biller.suppliers.select') }}",
            dataType: 'json',
            type: 'POST',
            quietMillis: 50,
            data: ({term}) => ({keyword: term}),
            processResults: function(data) {
                return {results: data.map(v => ({id: v.id+'-'+v.taxid, text: v.name+' : '+v.email}))}; 
            },
        }
    });

    // On adding paid values
    $('#billsTbl').on('change', '.paid', function() {
        const amount = $(this).parents('tr').find('.amount').text().replace(/,/g, '') * 1;
        const paid = $(this).val().replace(/,/g, '') * 1;
        if (paid > amount) $(this).val(amount.toLocaleString());
        calcTotal();
    });

    // bill row
    function billRow(v, i) {
        const amount = parseFloat(v.grandttl - v.amountpaid).toLocaleString();
        return `
            <tr>
                <td class="text-center">${new Date(v.due_date).toDateString()}</td>
                <td>${v.tid}</td>
                <td class="text-center">${v.note}</td>
                <td>${v.status}</td>
                <td class="text-center amount"><b>${amount}</b></td>
                <td><input type="text" class="form-control paid" name="paid[]"></td>
                <input type="hidden" name="bill_id[]" value="${v.id}">
            </tr>
        `;
    }

    // load bills
    $('#supplierbox').change(function() {
        const supplier_id = $(this).val().split('-')[0];
        $('#supplierid').val(supplier_id);
        // ajax call
        $.ajax({
            url: "{{ route('biller.bills.supplier_bills') }}?id=" + supplier_id,
            success: result => {
                $('#billsTbl tbody tr:not(:eq(-1))').remove();
                if (!result.length) return;
                result.forEach((v, i) => {
                    $('#billsTbl tbody tr:eq(-1)').before(billRow(v, i));
                });
            }
        });
    });

    // On deposit change
    $('#deposit').on('focus', function(e) {
        if (!$('#supplierbox').val()) $(this).blur();
    });
    $('#deposit').change(function() {
        let amountSum = 0;
        let depoSum = 0;
        let depo = $(this).val().replace(/,/g, '') * 1;
        $(this).val(parseFloat(depo).toLocaleString());
        $('#billsTbl tbody tr').each(function(i) {
            if ($('#billsTbl tbody tr:last').index() == i) return;
            const amount = $(this).find('.amount').text().replace(/,/g, '') * 1;
            if (depo > amount) $(this).find('.paid').val(amount.toLocaleString());
            else if (depo > 0) $(this).find('.paid').val(depo.toLocaleString());
            else $(this).find('.paid').val(0);
            const paid = $(this).find('.paid').val().replace(/,/g, '') * 1;
            depo -= amount;
            amountSum += amount;
            depoSum += paid;
        });
        $('#amount_ttl').val(amountSum.toLocaleString());
        $('#deposit_ttl').val(depoSum.toLocaleString());
    });

    function calcTotal() {
        let amountSum = 0;
        let depoSum = 0;
        $('#billsTbl tbody tr').each(function(i) {
            if ($('#billsTbl tbody tr:last').index() == i) return;
            const amount = $(this).find('.amount').text().replace(/,/g, '') * 1;
            const paid = $(this).find('.paid').val().replace(/,/g, '') * 1;
            amountSum += amount;
            depoSum += paid;
        });
        $('#amount_ttl').val(parseFloat(amountSum.toFixed(2)).toLocaleString());
        $('#deposit_ttl').val(parseFloat(depoSum.toFixed(2)).toLocaleString());
    }
</script>
@endsection