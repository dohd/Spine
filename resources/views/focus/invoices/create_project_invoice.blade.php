@extends ('core.layouts.app')
@section('title', trans('labels.backend.invoices.create'))
@section('page-header')
    <h1>
        {{ trans('labels.backend.invoices.management') }}
        <small>{{ trans('labels.backend.invoices.create') }}</small>
    </h1>
@endsection
@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-body">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <form method="post" id="data_form">
                                <div class="row">
                                    <div class="col-sm-6 cmp-pnl">
                                        <div id="customerpanel" class="inner-cmp-pnl">
                                            <div class="form-group row">
                                                <div class="col-sm-8"><label for="payer"
                                                        class="caption">Customer Name*</label>
                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-file-text-o"
                                                                aria-hidden="true"></span>
                                                        </div>
                                                        {{ Form::text('customer_name', $customer->company, ['class' => 'form-control round required', 'placeholder' => 'Customer Name', 'id' => 'payer-name', 'readonly' => 'readonly']) }}
                                                    </div>
                                                </div>
                                                <div class="col-sm-4"><label for="taxid" class="caption">KRA
                                                        PIN</label>
                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-bookmark-o"
                                                                aria-hidden="true"></span>
                                                        </div>
                                                        {{ Form::text('taxid', $customer->taxid, ['class' => 'form-control round', 'placeholder' => 'Tax Id', 'id' => 'taxid']) }}
                                                    </div>
                                                </div>
                                                {{ Form::hidden('customer_id', $customer->id, ['id' => 'customer_id']) }}
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-sm-6"><label for="refer_no"
                                                        class="caption">Bank Account*</label>
                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-bookmark-o"
                                                                aria-hidden="true"></span>
                                                        </div>
                                                        <select class="form-control round select-box required" name="bank_id"
                                                            id="bank_id" required>
                                                            <option value="0">-- Select Bank --</option>
                                                            @foreach ($banks as $bank)
                                                                <option value="{{ $bank->id }}">{{ $bank->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6"><label for="tid" class="caption">Select
                                                        {{ trans('general.tax') }}*</label>
                                                    <div class="input-group">
                                                        <select class="form-control round required" name='tax_id' id="tax_id" >
                                                            <option value="16" selected>16% VAT</option>
                                                            <option value="8">8% VAT</option>
                                                            <option value="0">Off</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                         
                                            <div class="form-group row">
                                                <div class="col-sm-6"><label for="refer_no"
                                                        class="caption">Ledger Account Asset(Debit)*</label>
                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-bookmark-o"
                                                                aria-hidden="true"></span>
                                                        </div>
                                                        <select name="dr_account_id" class="form-control round required"
                                                            id="dr_account_id">
                                                            <option value="">Select Ledger Account</option>
                                                            @foreach ($receivables as $account)
                                                                <option value="{{ $account->id }}"> {{ $account->holder }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6"><label for="tid" class="caption">Ledger
                                                        Account/Income Account(Credit)*</label>
                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-file-text-o"
                                                                aria-hidden="true"></span>
                                                        </div>
                                                        <select name="cr_account_id" class="form-control round required"
                                                            id="cr_account_id">
                                                            <option value="">Select Ledger Account</option>
                                                            @foreach ($income_accounts as $account)
                                                                <option value="{{ $account->id }}">
                                                                    {{ $account->holder }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 cmp-pnl">
                                        <div class="inner-cmp-pnl">
                                            <div class="form-group row">
                                                <div class="col-sm-4"><label for="tid"
                                                        class="caption">Transaction ID*</label>
                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-file-text-o"
                                                                aria-hidden="true"></span>
                                                        </div>
                                                        {{ Form::number('tid', @$last_tr->tid + 1, ['class' => 'form-control round', 'placeholder' => trans('purchaseorders.tid')]) }}
                                                    </div>
                                                </div>
                                                <div class="col-sm-4"><label for="invoice_no"
                                                        class="caption">Invoice Number*</label>
                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-bookmark-o"
                                                                aria-hidden="true"></span>
                                                        </div>
                                                        {{ Form::text('invoice_no', @$last_invoice->tid + 1, ['class' => 'form-control round required', 'placeholder' => trans('general.reference')]) }}
                                                    </div>
                                                </div>
                                                <div class="col-sm-4"><label for="lpo_ref"
                                                    class="caption">LPO Ref*</label>
                                                <div class="input-group">
                                                    <div class="input-group-addon"><span class="icon-bookmark-o"
                                                            aria-hidden="true"></span>
                                                    </div>
                                                    {{ Form::text('lpo_ref', null, ['class' => 'form-control round required', 'placeholder' =>'LPO Reference']) }}
                                                </div>
                                            </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-sm-6"><label for="invoicedate"
                                                        class="caption">Invice Date*</label>
                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-calendar4"
                                                                aria-hidden="true"></span>
                                                        </div>
                                                        {{ Form::text('invoicedate', null, ['class' => 'form-control round required', 'placeholder' => trans('purchaseorders.invoicedate'), 'data-toggle' => 'datepicker', 'autocomplete' => 'false']) }}
                                                    </div>
                                                </div>

                                                <div class="col-sm-6"><label for="validity" class="caption">Validity Period*</label>
                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
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



                                            
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-sm-12">
                                                    <label for="toAddInfo"
                                                        class="caption">{{ trans('general.note') }}*</label>
                                                    {{ Form::textarea('notes', null, ['class' => 'form-control round required', 'placeholder' => trans('general.note'), 'rows' => '2']) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane active in" id="active1" aria-labelledby="active-tab1" role="tabpanel">
                                    <div id="saman-row">
                                        <table class="table-responsive tfr my_stripe">
                                            <thead>
                                                <tr class="item_header bg-gradient-directional-blue white ">
                                                    <th width="35%" class="text-center">Description</th>
                                                    <th width="25%" class="text-center">Reference</th>
                                                    <th width="10%" class="text-center">UOM</th>
                                                    <th width="10%" class="text-center">Qty</th>
                                                    <th width="10%" class="text-center">Rate Exclusive</th>
                                                    <th width="10%" class="text-center">{{ trans('general.amount') }}
                                                        ({{ config('currency.symbol') }})
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $sub_total = 0;
                                                    $tax_total = 0;
                                                    $grand_total = 0;
                                                    $i = 0;
                                                @endphp
                                                @foreach ($quotes as $quote)
                                                    @php
                                                        $i++;
                                                        $ref = '';
                                                        if ($quote->verified_jcs) {
                                                            $ref_array = [];
                                                            foreach ($quote->verified_jcs as $ref_row) {
                                                                $ref = 'JC-' . $ref_row->reference;
                                                                if ($ref_row->type == 2) {
                                                                    $ref = 'DN-' . $ref_row->reference;
                                                                }
                                                                $ref_array[] = $ref;
                                                            }
                                                            $ref = implode(', ', $ref_array);
                                                        }
                                                        $tid = sprintf('%04d', $quote->tid);
                                                        if ($quote->bank_id) {
                                                            $tids = 'PI-' . $tid;
                                                        } else {
                                                            $tids = 'QT-' . $tid;
                                                        }
                                                        $sub_total += $quote->verified_amount;

                                                        $note=$quote->notes;
                                                        if(isset($quote->branch_id)){

                                                            $note=$quote->customer_branch->name.'-'.$quote->notes;

                                                        }
                                                    @endphp
                                                    <tr>
                                                        <td><input type="text" value="{{ $note }}"
                                                                class="form-control" name="description[]"
                                                                id='description-{{ $i }}'></td>
                                                        <td><input type="text" class="form-control req amnt"
                                                                name="reference[]" id="reference-{{ $i }}"
                                                                autocomplete="off"
                                                                value="{{ $tids }} : {{ $ref }}"></td>
                                                        <td><input type="text" class="form-control " name="unit[]"
                                                                id="unit-{{ $i }}" value="Lot"
                                                                autocomplete="off"></td>
                                                        <td><input type="text" class="form-control" value="1" name="product_qty[]"
                                                                id="product_qty-{{ $i }}" autocomplete="off" readonly>
                                                        </td>
                                                        <td><input type="text" class="form-control  " name="product_price[]"
                                                                id="product_price-{{ $i }}"
                                                                value="{{ $quote->verified_amount }}" autocomplete="off"
                                                                readonly></td>
                                                        <td><span
                                                                class="currenty">{{ config('currency.symbol') }}</span>
                                                            <strong><span class='ttlText'
                                                                    id="result-{{ $i }}">{{ $quote->verified_amount }}</span></strong>
                                                        </td>
                                                        <input type="hidden" name="quote_id[]"
                                                            id="quote_id-{{ $i }}" value="{{ $quote->id }}">
                                                        <input type="hidden" class="pdIn" name="project_id[]"
                                                            id="project_id-{{ $i }}"
                                                            value="{{ $quote->project_quote_id }}">
                                                        <input type="hidden" class="pdIn" name="branch_id[]"
                                                            id="branch_id-{{ $i }}"
                                                            value="{{ $quote->branch_id }}">
                                                    </tr>
                                                @endforeach
                                                @php
                                                    $tax_total = $sub_total * 0.16;
                                                    $grand_total = $sub_total * 1.16;
                                                @endphp
                                                <tr class="sub_c" style="display: table-row;">
                                                    <td colspan="4" align="right">
                                                        {{ Form::hidden('subtotal',  number_format($sub_total, 2), ['id' => 'subttlform']) }}
                                                        <strong>Sub Total ({{ config('currency.symbol') }}) </strong>
                                                    </td>
                                                    <td align="left" colspan="2">
                                                        <span id="taxr"
                                                            class="lightMode">{{ number_format($sub_total, 2) }}</span>
                                                    </td>
                                                    
                                                </tr>
                                                <tr class="sub_c" style="display: table-row;">
                                                    <td colspan="4" align="right">
                                                        <strong>Total Tax</strong>
                                                    </td>
                                                    <td align="left" colspan="2"><input type="text"
                                                            value="{{ number_format($tax_total, 2) }}" name="tax"
                                                            class="form-control" id="tax"
                                                            value="{{ $sub_total }}" readonly=""></td>
                                                </tr>
                                                <tr class="sub_c" style="display: table-row;">
                                                    <td colspan="4" align="right">
                                                        <strong>{{ trans('general.grand_total') }}
                                                            (<span
                                                                class="currenty lightMode">{{ config('currency.symbol') }}</span>)</strong>
                                                    </td>
                                                    <td align="left" colspan="2"><input type="text" name="total"
                                                            class="form-control" id="total"
                                                            value="{{ number_format($grand_total, 2) }}" readonly="">
                                                    </td>
                                                </tr>
                                                <tr class="sub_c" style="display: table-row;">
                                                    <td colspan="2"></td>
                                                    <td align="right" colspan="5"><input type="submit"
                                                            class="btn btn-success sub-btn btn-lg" value="Create Invoice"
                                                            id="submit-data" data-loading-text="Creating...">
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!---endtab1-->
                                <!---endtab2-->
                                <input type="hidden" value="new_i" id="inv_page">
                                {{ Form::hidden('term_id','1', ['id' => 'term_id']) }}
                                <input type="hidden" value="{{ route('biller.invoices.store_project_invoice') }}"
                                    id="action-url">
                                <input type="hidden" value="search" id="billtype">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('extra-scripts')
    {{ Html::script('core/app-assets/vendors/js/extensions/sweetalert.min.js') }}
    <script type="text/javascript">
        // $("#project_id").select2();
        $(function() {
            $('[data-toggle="datepicker"]').datepicker({
                autoHide: true,
                format: '{{ config('core.user_date_format') }}'
            });
            $('[data-toggle="datepicker"]').datepicker('setDate', '{{ date(config('core.user_date_format')) }}');
            editor();
        });
        $("input[name=payer_type]").on('change', function() {
            var p_t = $('input[name=payer_type]:checked').val();
            if (p_t != 'walkin') {
                $('#suppliers-box').attr('readonly', false);
                $('#suppliers-box').val('');
                $('#taxid').val('');
                $('#payer-name').val('');
                $('#taxid').attr('readonly', true);
                $('#payer-name').attr('readonly', true);
                $('#payer_id').val('');
            } else {
                $('#suppliers-box').attr('readonly', true);
                $('#suppliers-box').val('');
                $('#taxid').val('');
                $('#payer-name').val('');
                $('#taxid').attr('readonly', false);
                $('#payer-name').attr('readonly', false);
                $('#payer_id').val('');
            }
        });
        $(".user-box-new").keyup(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var box_id = $(this).attr('data-section');
            var p_t = $('input[name=payer_type]:checked').val();
            $.ajax({
                type: "POST",
                url: baseurl + 'transactions/payer_search',
                data: 'keyword=' + $(this).val() + '&payer_type=' + p_t,
                beforeSend: function() {
                    $("#" + box_id + "-box").css("background", "#FFF url(" + baseurl +
                        "assets/custom/load-ring.gif) no-repeat 165px");
                },
                success: function(data) {
                    $("#" + box_id + "-box-result").show();
                    $("#" + box_id + "-box-result").html(data);
                    $("#" + box_id + "-box").css("background", "none");
                }
            });
        });
        function selectPayer(data) {
            $('#payer_id').val(data.id);
            $('#relation_id').val(data.relation_id);
            $('#payer-name').val(data.name);
            //console.log(data);
            $('#taxid').val(data.taxid);
            $("#suppliers-box-result").hide();
        }
        $('#tax_id').change(function() {
            var tax_id = $('#tax_id').val()
            var subtotal = accounting.unformat($("#subtotal").val(), accounting.settings.number.decimal);
            var tax = subtotal * tax_id / 100;
            $('#tax_total').val(accounting.formatNumber(tax));
            $('#geand_total').val(accounting.formatNumber(tax + subtotal));
            console.log(subtotal);
            // console.log(lpo_number);
        });
    </script>
@endsection
