@extends ('core.layouts.app')

@section('title', trans('labels.backend.invoices.create'))

@section('content')
<div class="content-wrapper">
    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::open(['route' => 'biller.invoices.store_project_invoice', 'method' => 'POST']) }}
                        <div class="row mb-1">
                            <div class="col-3"><label for="payer" class="caption">Customer Name*</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                    {{ Form::text('customer_name', $customer->company, ['class' => 'form-control round', 'id' => 'customername', 'readonly']) }}
                                    <input type="hidden" name="customer_id" value="{{ $customer->id }}" id="customer_id">
                                </div>
                            </div>
                            <div class="col-3">
                                <label for="taxid" class="caption">KRA PIN</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                                    {{ Form::text('taxid', $customer->taxid, ['class' => 'form-control round', 'required']) }}
                                </div>
                            </div>
                            <div class="col-3"> 
                                <label for="refer_no" class="caption">Bank Account*</label>                                   
                                <div class="input-group">
                                    <select class="form-control round select-box required" name="bank_id" id="bank_id" required>
                                        <option value="">-- Select Bank --</option>
                                        @foreach ($banks as $bank)
                                            <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                        @endforeach
                                    </select>
                                </div>                                
                            </div>
                            <div class="col-3">
                                <label for="tid" class="caption">Select {{ trans('general.tax') }}*</label>
                                <div class="input-group">
                                    <select class="form-control round" name='tax_id' id="tax_id">
                                        <option value="16" selected>16% VAT</option>
                                        <option value="8">8% VAT</option>
                                        <option value="0">Off</option>
                                    </select>
                                </div>
                            </div>                            
                        </div>

                        <div class="row mb-1">
                            <div class="col-3">
                                <label for="invoicedate" class="caption">Invoice Date*</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                                    {{ Form::text('invoicedate', null, ['class' => 'form-control round datepicker', 'id' => 'invoicedate']) }}
                                </div>
                            </div>
                            <div class="col-3">
                                <label for="validity" class="caption">Credit Period*</label>
                                <div class="input-group">
                                    <select class="form-control round  select-box" name="validity" id="validity">
                                        <option value="0" selected>On Reciept</option>
                                        <option value="14">Valid For 14 Days</option>
                                        <option value="30">Valid For 30 Days</option>
                                        <option value="45">Valid For 45 Days</option>
                                        <option value="60">Valid For 60 Days</option>
                                        <option value="90">Valid For 90 Days</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-3">
                                <label for="tid" class="caption">Transaction ID*</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                    @php
                                        $tid = isset($last_tr->tid) ? $last_tr->tid+1 : 1;
                                    @endphp
                                    {{ Form::text('tid', 'Inv-'.sprintf('%04d', $tid), ['class' => 'form-control round', 'disabled']) }}
                                    <input type="hidden" name="tid" value={{ $tid }}>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-1">
                            <div class="col-4">                                
                                <div class="input-group">
                                    <label for="ledger-off">Enable Ledger Account</label>
                                </div>
                                <select class="form-control" name="ledger_toggle" id="ledgertoggle">
                                    <option value="0" selected>No</option>
                                    <option value="1">Yes</option>                                    
                                </select>
                            </div>
                            <div class="col-4">                                
                                <div class="input-group">
                                    <label for="refer_no" class="caption">Ledger Account Asset (Debit)*</label>
                                </div>
                                <select name="dr_account_id" class="form-control" id="dr_account" required disabled>
                                    <option value="">-- Select Ledger Account--</option>
                                    @foreach ($receivables as $account)
                                        <option value="{{ $account->id }}"> {{ $account->holder }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-4">
                                <div class="input-group">
                                    <label for="tid" class="caption">Ledger Account/Income Account (Credit)*</label>
                                </div>
                                <select name="cr_account_id" class="form-control" id="cr_account" required disabled>
                                    <option value="">-- Select Ledger Account --</option>
                                    @foreach ($income_accounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->holder }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>                     
                        
                        <div class="row mb-1">
                            <div class="col-12">
                                <div class="input-group"><label for="title" class="caption">Note</label></div>
                                {{ Form::text('notes', null, ['class' => 'form-control']) }}
                            </div>
                        </div>

                        <table id="quotation" class="table-responsive tfr my_stripe_single pb-1">
                            <thead>
                                <tr class="item_header bg-gradient-directional-blue white">
                                    <th class="text-center">#</th>
                                    <th width="25%" class="text-center">Reference</th>
                                    <th width="35%" class="text-center">Description</th>
                                    <th width="10%" class="text-center">UOM</th>
                                    <th width="10%" class="text-center">Qty</th>
                                    <th width="10%" class="text-center">Rate (VAT exc)</th>
                                    <th width="10%" class="text-center">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($quotes as $k => $val)
                                    @php
                                        // Reference details
                                        $tid = sprintf('%04d', $val->tid);
                                        $tid = $val->bank_id ? 'PI-'.$tid : 'QT-'.$tid;
                                        $lpo_no = $val->lpo ? $val->lpo->lpo_no : '';
                                        $client_ref = $val->client_ref;
                                        $branch_name = $val->branch->name;
                                        if (isset($val->branch->code)) {
                                            $branch_name = $branch_name . '(' . $val->branch->code . ')';
                                        }                                       

                                        // Description details
                                        $title = $val->notes;
                                        $jcs = array();
                                        foreach($val->verified_jcs as $jc) {
                                            if ($jc->type == 2) $jcs[] = 'DN-'.$jc->reference;
                                            else $jcs[] = 'JC-'.$jc->reference;
                                        }

                                        // Table values
                                        $reference = implode('; ', [$branch_name, $tid, $lpo_no, $client_ref]);
                                        $description = $title . '; ' . implode(', ', $jcs);
                                        $price = number_format($val->subtotal, 2);
                                        $project_id = $val->project_quote->project_id;
                                    @endphp
                                    <tr>
                                        <td>{{ $k+1 }}</td>                
                                        <td><textarea class="form-control" name="reference[]" id="reference-{{ $k }}" readonly>{{ $reference }}</textarea></td>
                                        <td><textarea type="text" class="form-control" name="description[]" id="description-{{ $k }}">{{ $description }}</textarea></td>
                                        <td><input type="text" class="form-control " name="unit[]" id="unit-{{ $k }}" value="Lot" readonly></td>
                                        <td><input type="text" class="form-control" name="product_qty[]" id="product_qty-{{ $k }}" value="1" readonly></td>
                                        <td><input type="text" class="form-control" name="product_price[]" value="{{ $price }}" id="product_price-{{ $k }}" readonly></td>
                                        <td><strong><span class='ttlText' id="result-{{ $k }}">{{ $price }}</span></strong></td>
                                        <input type="hidden" value="{{ $price }}" id="initprice-{{ $k }}" disabled>
                                        <input type="hidden" name="quote_id[]" value="{{ $val->id }}" id="quoteid-{{ $k }}">
                                        <input type="hidden" name="branch_id[]" value="{{ $val->branch_id }}" id="branchid-{{ $k }}">
                                        <input type="hidden" name="project_id[]" value="{{ $project_id }}" id="projectid-{{ $k }}">
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="row">
                            <div class="col-9">
                                <div class="input-group">
                                    <label for="notes" class="caption font-weight-bold">Remark</label>
                                </div>
                                {{ Form::textarea('remark', null, ['class' => 'form-control text-danger', 'rows' => '4']) }}
                            </div>
                            <div class="col-3">
                                <div>
                                    <label for="subtotal" class="caption font-weight-bold">Subtotal</label>
                                    <input type="text" class="form-control mb-1" name="subtotal" id="subtotal" readonly>
                                </div>                                                         
                                <div>
                                    <label for="totaltax" class="caption font-weight-bold">Total Tax</label>
                                    <input type="text" class="form-control mb-1" name="tax" id="tax" readonly>
                                </div>
                                <div>
                                    <label for="grandtotal" class="caption font-weight-bold">Grand Total</label>
                                    <input type="text" class="form-control mb-1" name="total" id="total" readonly>
                                </div> 
                                {{ Form::submit('Create Invoice', ['class' => 'btn btn-primary btn-lg']) }}                          
                            </div>
                        </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
{{ Html::script('core/app-assets/vendors/js/extensions/sweetalert.min.js') }}

<script type="text/javascript">
    // Initialize datepicker
    $('.datepicker')
        .datepicker({ format: "{{config('core.user_date_format')}}"})
        .datepicker('setDate', new Date());

    // Disable Account ledgers
    $('#ledgertoggle').change(function() {
        $('#dr_account').attr('disabled', true);
        $('#cr_account').attr('disabled', true);
        if ($(this).val() == 1) {
            $('#dr_account').attr('disabled', false);
            $('#cr_account').attr('disabled', false);
        }
    });

    // On selecting Tax
    $('#tax_id').change(function() {
        let total = 0;
        let subtotal = 0; 
        const taxRate = $('#tax_id').val() / 100;
        $('#quotation tbody tr').each(function(i) {
            const subtStr = $('#initprice-'+i).val().replace(/,/g, '');
            const rateExc = parseFloat(subtStr);
            const rateInc = rateExc + (taxRate * rateExc);
            subtotal += rateExc;
            total += rateInc;
            // update table values
            const $rateInput = $(this).find('td').eq(5).children();
            const $amountSpan = $(this).find('td').eq(6).children();
            $rateInput.val(rateInc.toLocaleString());
            $amountSpan.text(rateInc.toLocaleString());
        });
        $('#subtotal').val(subtotal.toLocaleString());
        $('#total').val(total.toLocaleString());
        $('#tax').val((total - subtotal).toLocaleString());
    });
    $('#tax_id').trigger('change');
</script>
@endsection