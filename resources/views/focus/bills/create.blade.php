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
                                    @foreach (['eft', 'rtg','cash', 'mpesa', 'cheque'] as $val)
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
                                <label for="payment_mode">Document Type</label>
                                <select name="doc_ref_type" id="" class="form-control" required>
                                   <option value="">-- Select Type --</option>
                                    @foreach (['receipt', 'dnote', 'voucher'] as $val)
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
                                    <th width="10%" class="text-center">#</th>
                                    <th width="10%">Bill Number</th>
                                    <th width="40%" class="text-center">Note</th>
                                    <th width="10%">Status</th>
                                    <th width="15%" class="text-center">Amount (VAT Inc)</th>
                                    <th width="15%" class="text-center">Deposited (Ksh.)</th>
                                </tr>
                            </thead>
                            <tbody>                                
                                <tr>
                                    <td colspan="4"></td>
                                    <td colspan="2">
                                        <div class="row no-gutters mb-1">
                                            <div class="col-6 pl-3 pt-1"><b>Total Bill Amount:</b></div>
                                            <div class="col-6">
                                                 {{ Form::text('amount_ttl', 0, ['class' => 'form-control', 'id' => 'amount_ttl', 'readonly']) }}
                                            </div>                          
                                        </div>
                                        <div class="row no-gutters">
                                            <div class="col-6 pl-3 pt-1"><b>Total Deposited:</b></div>
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
        const amount = $(this).parents('tr').find('.amount').text().replace(/,/g, '');
        if (paid > amount) $(this).val((amount*1).toLocaleString());
    });

    // bill row
    function billRow(v, i) {
        const amount = parseFloat(v.grandttl).toLocaleString();
        return `
            <tr>
                <td class="text-center">${i+1}</td>
                <td>${v.tid}</td>
                <td class="text-center">${v.note}</td>
                <td>${v.status}</td>
                <td class="text-center amount"><b>${amount}</b></td>
                <td><input type="text" class="form-control paid" name="paid[]" value=""></td>
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
                if (!result.length) $('#billsTbl tbody tr:not(:eq(-1))').remove();
                result.forEach((v, i) => {
                    $('#billsTbl tbody tr:eq(-1)').before(billRow(v, i));
                });
            }
        });
    });

    // On deposit change
    $('#deposit').change(function() {
        const depo = $(this).val().replace(/,/g, '');
        $(this).val(parseFloat(depo).toLocaleString());

        let init = depo*1;
        let amountSum = 0;
        let depoSum = 0;
        const rows = $('#billsTbl tbody tr').length;
        $('#billsTbl tbody tr').each(function() {
            if ($(this).index() == rows-1) return;

            const amount = $(this).find('.amount').text().replace(/,/g, '');
            $(this).find('.paid').val(0);

            if (init > amount){
                $(this).find('.paid').val((amount*1).toLocaleString());
            } else if (init > 0) $(this).find('.paid').val((init*1).toLocaleString());
            const paid = $(this).find('.paid').val().replace(/,/g, '');
            
            init -= amount;
            amountSum += amount*1;
            depoSum += paid*1;
        });

        $('#amount_ttl').val(amountSum.toLocaleString());
        $('#deposit_ttl').val(depoSum.toLocaleString());
    });
</script>
@endsection
