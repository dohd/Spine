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
                                    <input type="hidden" name="tid" value=1>
                                </div>
                            </div>  
                            <div class="col-4">
                                <label for="ledgerAccount" class="caption">Ledger Account</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                    <select class="form-control" name="account_id" id="account_id" required>
                                        <option value="">-- Select Ledger Account --</option>
                                        @foreach ($accounts as $row)
                                            <option value="{{ $row->id }}">{{ $row->holder }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>                          
                        </div> 

                        <div class="row mb-1">
                            
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

                        <table class="table-responsive tfr my_stripe_single" id="invoice">
                            <thead>
                                <tr class="item_header bg-gradient-directional-blue white">
                                    <th width="10%" class="text-center">#</th>
                                    <th width="10%">Invoice No.</th>
                                    <th width="45%" class="text-center">Description</th>
                                    <th width="20%" class="text-center">Amount (VAT Inc)</th>
                                    <th width="20%" class="text-center">Deposit (Ksh.)</th>
                                </tr>
                            </thead>
                            <tbody></tbody>                
                        </table>

                        <div class="row mt-1">                            
                            <div class="col-3 ml-auto"> 
                                <div class="mb-1">
                                    <label for="total" class="caption"><b>Total Amount</b></label>                               
                                    <div class="input-group">                                    
                                        {{ Form::text('total', 0, ['class' => 'form-control', 'id' => 'total', 'readonly']) }}
                                    </div> 
                                </div>
                                <div>
                                    <label for="amount" class="caption"><b>Total Deposit</b></label>
                                    <div class="input-group">
                                        <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                        {{ Form::text('total_depo', null, ['class' => 'form-control round', 'step' => 2, 'id' => 'totalDepo', 'required']) }}
                                    </div>
                                </div>                                
                                {{ Form::submit('Create Payment', ['class' => 'btn btn-primary btn-lg mt-1']) }}                          
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

    // On adding deposit
    $('#invoice').on('change', '.depo', function() {
       return calcTotal();
    });

    // fetch customer invoices
    $('#person').change(function() {
        $.ajax({
            url: "{{ route('biller.invoices.client_invoices') }}?client_id=" + $(this).val(),
            success: function(data) {
                if (!data.length) return $('#invoice tbody').html('');
                data.forEach((v, i) => {
                    $('#invoice tbody').append(invoiceRow(v, i));
                });
                calcTotal();
            }
        });
    });

    // invoice rows
    function invoiceRow(v, i) {
        const total = parseFloat(v.total).toLocaleString();
        return `
            <tr>
                <td class="text-center">${i+1}</td>
                <td>${v.tid}</td>
                <td class="text-center">${v.notes}</td>
                <td class="text-center" id="amount-${i}">${total}</td>
                <td><input type="text" class="form-control depo" name="depo[]" id="depo-${i}" value="0"></td>
                <input type="hidden" name="invoice_id[]" value="${v.id}" id="invoiceid-${i}">
            </tr>
        `;
    }

    function calcTotal() {
        let total = 0;
        let paid = 0;
        $('#invoice tbody tr').each(function() {
            const i = $(this).index();
            const amount = $('#amount-'+i).text().replace(/,/g, '');
            const depo = $('#depo-'+i).val().replace(/,/g, '');
            $('#depo-'+i).val((depo*1).toLocaleString());

            total += amount*1;
            paid += depo*1;
        });
        $('#total').val(total.toLocaleString());
        $('#totalDepo').val(paid.toLocaleString());
    }
</script>
@endsection
