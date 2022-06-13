@extends ('core.layouts.app')

@section('title', 'Invoice Payment | Receive')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Invoice Payment</h4>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::open(['route' => 'biller.invoices.store_payment', 'method' => 'POST', 'id' => 'invoicePay']) }}
                        <div class="row mb-1">
                            <div class="col-5">
                                <label for="customer" class="caption">Search Customer</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                    <select id="person" name="customer_id" class="form-control select-box" data-placeholder="Search Customer" required>
                                    </select>
                                </div>
                            </div>

                            <div class="col-2">
                                <label for="reference" class="caption">Transaction ID</label>
                                <div class="input-group">
                                    {{ Form::text('tid', $tid+1, ['class' => 'form-control', 'id' => 'tid', 'readonly']) }}
                                </div>
                            </div> 

                            <div class="col-2">
                                <label for="date" class="caption">Payment Date</label>
                                <div class="input-group">
                                    {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required']) }}
                                </div>
                            </div>     
                            
                            <div class="col-2">
                                <label for="type">Receive on Account</label>
                                <select name="account_id" id="account" class="form-control" required>
                                    <option value="">-- Select Account --</option>
                                    @foreach ($accounts as $row)
                                        <option value="{{ $row->id }}">{{ $row->holder }}</option>
                                    @endforeach
                                </select>
                            </div>   
                        </div> 

                        <div class="form-group row">  
                            <div class="col-2">
                                <label for="deposit" class="caption">Amount (Ksh.)</label>
                                {{ Form::text('deposit', null, ['class' => 'form-control', 'id' => 'deposit', 'required']) }}
                            </div>  
                            <div class="col-3">
                                <label for="payment_mode">Payment Mode</label>
                                <select name="payment_mode" id="paymentMode" class="form-control" required>
                                    <option value="">-- Select Mode --</option>
                                    @foreach (['eft', 'rtgs','cash', 'mpesa', 'cheque'] as $val)
                                        <option value="{{ $val }}">{{ strtoupper($val) }}</option>
                                    @endforeach
                                </select>
                            </div>  
                            <div class="col-2">
                                <label for="reference" class="caption">Reference</label>
                                {{ Form::text('reference', null, ['class' => 'form-control', 'id' => 'reference', 'required']) }}
                            </div>      
                            <div class="col-2">
                                <label for="type">Allocation Type</label>
                                <select name="is_allocated" id="allocated" class="form-control" required>
                                    <option value="">-- Select Type --</option>
                                    @foreach (['On Account', 'Per Invoice',] as $k => $val)
                                        <option value="{{ $k }}">{{ $val }}</option>
                                    @endforeach
                                </select>
                            </div>                                                 
                        </div>

                        <div class="table-responsive">
                            <table class="table tfr my_stripe_single text-center" id="invoiceTbl">
                                <thead>
                                    <tr class="bg-gradient-directional-blue white">
                                        <th>Due Date</th>
                                        <th> Invoice Number</th>
                                        <th>Note</th>
                                        <th>Status</th>
                                        <th>Amount (VAT Inc)</th>
                                        <th>Allocate (Ksh.)</th>
                                    </tr>
                                </thead>
                                <tbody>                                
                                    <tr class="bg-white">
                                        <td colspan="4"></td>
                                        <td colspan="2">
                                            <div class="form-inline mb-1 float-right">
                                                <label for="total_bill">Total Amount</label>
                                                {{ Form::text('amount_ttl', 0, ['class' => 'form-control col-7 ml-1', 'id' => 'amount_ttl', 'readonly']) }}
                                            </div>
                                            <div class="form-inline float-right">
                                                <label for="total_paid">Total Allocated</label>
                                                {{ Form::text('deposit_ttl', 0, ['class' => 'form-control col-7 ml-1', 'id' => 'deposit_ttl', 'readonly']) }}
                                            </div>                                         
                                        </td>
                                    </tr>
                                </tbody>                
                            </table>
                        </div>
                        <div class="form-group row">                            
                            <div class="col-12">  
                                <input type="hidden" name="payment_id" id="paymentId">                              
                                {{ Form::submit('Receive Payment', ['class' =>'btn btn-primary btn-lg float-right mr-3']) }}
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

    $('form').submit(function(e) {
        // enable disabled attributes
        ['#paymentMode', '#allocated', '#account', '#date'].forEach(v => $(v).attr('disabled', false));
        // if allocated amount is 0 and allocation type is per invoice
        if ($('#deposit_ttl').val() == 0 && $('#allocated').val() == 1) {
            e.preventDefault();
            alert('Allocate payment amount on at least one invoice!');
        } else if ($('#deposit_ttl').val() == 0) {
            e.preventDefault();
            alert('Enter payment amount!');
        }
    });

    // datepicker
    $('.datepicker')
    .datepicker({format: "{{config('core.user_date_format')}}", autoHide: true})
    .datepicker('setDate', new Date())

    // 
    $('#person').select2({
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

    // On adding paid values
    $('#invoiceTbl').on('change', '.paid', function() {
        const amount = $(this).parents('tr').find('.amount').text().replace(/,/g, '') * 1;
        const paid = $(this).val().replace(/,/g, '') * 1;
        if (paid > amount) $(this).val(amount.toLocaleString());
        calcTotal();
    });

    // on change allocation type
    $('#allocated').change(function() {
        // on account
        if ($(this).val() == 0) {
            $('#invoiceTbl tbody tr').each(function() {
                $(this).find('.paid').val('').change();
            });
        }
    });    

    // invoice row
    function invoiceRow(v, i) {
        const amount = parseFloat(v.total - v.amountpaid).toLocaleString();
        return `
            <tr>
                <td class="text-center">${new Date(v.invoiceduedate).toDateString()}</td>
                <td>${v.tid}</td>
                <td class="text-center">${v.notes}</td>
                <td>${v.status}</td>
                <td class="text-center amount"><b>${amount}</b></td>
                <td><input type="text" class="form-control paid" name="paid[]"></td>
                <input type="hidden" name="invoice_id[]" value="${v.id}">
            </tr>
        `;
    }

    // on change customer
    $('#person').change(function() {
        $('#deposit').val('');
        // load client invoices
        $.ajax({
            url: "{{ route('biller.invoices.client_invoices') }}?id=" + $(this).val(),
            success: result => {
                $('#invoiceTbl tbody tr:not(:eq(-1))').remove();
                if (!result.length) return;
                result.forEach((v, i) => {
                    $('#invoiceTbl tbody tr:eq(-1)').before(invoiceRow(v, i));
                });
                calcTotal();
            }
        });
        // load unallocated
        $.ajax({
            url: "{{ route('biller.invoices.unallocated_payment') }}",
            type: 'POST',
            data: {customer_id: $(this).val()},
            success: data => {
                // console.log(data);
                ['#paymentMode', '#allocated', '#account'].forEach(v => $(v).attr('disabled', false).val(''));
                ['#deposit', '#reference'].forEach(v => $(v).attr('readonly', false).val('').change());
                $('#date').datepicker('setDate', new Date()).attr('disabled', false);
                if (data.hasOwnProperty('id')) {
                    const amount = data.deposit.replace(/,/g, '') * 1;
                    $('#deposit').val(parseFloat(amount.toFixed(2)).toLocaleString())
                    .attr('readonly', true).change();
                    $('#paymentMode').val(data.payment_mode).attr('disabled', true);
                    $('#reference').val(data.reference).attr('readonly', true);
                    $('#allocated').val(1).attr('disabled', true);
                    $('#account').val(data.account.id).attr('disabled', true);
                    $('#date').datepicker('setDate', new Date(data.date)).attr('disabled', true);
                    $('#paymentId').val(data.id);
                }
            }
        });
    });

    // On deposit change
    $('#deposit').on('focus', function(e) {
        if (!$('#person').val()) $(this).blur();
    });
    $('#deposit').change(function() {
        let amountSum = 0;
        let depoSum = 0;
        let depo = $(this).val().replace(/,/g, '') * 1;
        $(this).val(parseFloat(depo).toLocaleString());
        const rows = $('#invoiceTbl tbody tr').length;
        $('#invoiceTbl tbody tr').each(function() {
            if ($(this).index() == rows-1) return;
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
        const rows = $('#invoiceTbl tbody tr').length;
        $('#invoiceTbl tbody tr').each(function() {
            if ($(this).index() == rows-1) return;
            const amount = $(this).find('.amount').text().replace(/,/g, '') * 1;
            const paid = $(this).find('.paid').val().replace(/,/g, '') * 1;

            amountSum += amount;
            depoSum += paid;
        });
        $('#amount_ttl').val(amountSum.toLocaleString());
        $('#deposit_ttl').val(depoSum.toLocaleString());
    }
</script>
@endsection
