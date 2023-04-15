<div class="row">
    <!-- Quote -->
    <div class="col-6">
        <h3 class="form-group">
            @php
                $title_arr = explode(' ', $words['title']);
                $title = implode(' ', [$title_arr[0], ...array_splice($title_arr, 1)]);
            @endphp
            {{ $title }}
        </h3>
        <div class="form-group row">
            <div class="col-12">
                <label for="ticket">Ticket</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                    <select class="form-control" name="lead_id" id="lead_id" data-placeholder="Search by Ticket No, Subject, Client, Branch" required> 
                        <option value=""></option>                                                
                        @foreach ($leads as $lead)
                            @php
                                $customer_name = '';
                                if ($lead->customer) {
                                    $customer_name .= $lead->customer->company;
                                    if ($lead->branch) $customer_name .= " - {$lead->branch->name}";
                                } else $customer_name = $lead->client_name;
                                
                                // create mode
                                $prefix = $prefixes[1];
                                if (isset($quote)) $prefix = $prefixes[2]; //edit mode
                            @endphp
                            <option 
                                value="{{ $lead->id }}" 
                                title="{{ $lead->title }}" 
                                client_ref="{{ $lead->client_ref }}"
                                customer_id="{{ $lead->client_id }}"
                                branch_id="{{ $lead->branch_id }}"
                                assign_to="{{ $lead->assign_to }}"
                                {{ $lead->id == @$quote->lead_id ? 'selected' : '' }}
                            >
                                {{ gen4tid("{$prefix}-", $lead->reference) }} - {{ $customer_name }} - {{ $lead->title }}
                            </option>
                        @endforeach                                                                                             
                    </select>
                    <input type="hidden" name="branch_id" id="branch_id" value="0">
                    <input type="hidden" name="customer_id" id="customer_id" value="0">
                </div>
            </div>
        </div>

        <div class="form-group row">
            <div class='col-6'>
                <label for="print_type" >Print Type</label>
                <div>                    
                    <div class="d-inline-block custom-control custom-checkbox mr-1">
                        <input type="radio" class="custom-control-input bg-primary" name="print_type" value="inclusive" id="colorCheck6">
                        <label class="custom-control-label" for="colorCheck6">VAT-Inclusive</label>
                    </div>
                    <div class="d-inline-block custom-control custom-checkbox">
                        <input type="radio" class="custom-control-input bg-purple" name="print_type" value="exclusive" id="colorCheck7" checked>
                        <label class="custom-control-label" for="colorCheck7">VAT-Exclusive</label>
                    </div>
                </div>
            </div>
            
            <div class="col-3">
                <label for="customer">Pre-agreed Pricing</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                    <select id="price_customer" name="price_customer_id" class="custom-select">
                        <option value="">Default </option>
                        @foreach($price_customers as $row)
                        <option value="{{ $row->id }}">{{ $row->company }}</option>
                    @endforeach
                    </select>
                </div>
            </div>

            <div class="col-3">
                <label for="serial_no" >{{ trans('general.serial_no') }}.</label>
                <div class="input-group">
                    <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span></div>
                    @php
                        $tid = isset($words['edit_mode'])? $quote->tid : $lastquote->tid+1;
                        $tid_prefix = !isset($words['edit_mode'])? $prefixes[0] : ($quote->bank_id? $prefixes[1] : $prefixes[0]);
                    @endphp
                    {{ Form::text('tid', gen4tid("{$tid_prefix}-", $tid), ['class' => 'form-control round', 'id' => 'tid', 'disabled']) }}
                    <input type="hidden" name="tid" value="{{ $tid }}">
                </div>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-4">
                <label for="attention">Attention</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                    {{ Form::text('attention', null, ['class' => 'form-control round', 'placeholder' => 'Attention', 'id' => 'attention']) }}
                </div>
            </div>
            <div class="col-4">                
                <label for="prepared_by">Prepared By</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                    {{ Form::text('prepared_by', null, ['class' => 'form-control round', 'placeholder' => 'Prepaired By', 'id' => 'prepared_by']) }}
                </div>
            </div>
            <div class="col-4">
                <label for="quote_type">{{ $is_pi? 'Proforma Invoice' : 'Quote' }} Type</label>
                <select name="quote_type" id="quote_type" class="custom-select" required>
                    @php
                        $selected = '';
                    @endphp
                    @foreach (['standard', 'project'] as $val)
                        @php
                            if (isset($quote)) $selected = ($quote->quote_type == $val)? 'selected' : '';
                            else $selected = $val? 'selected' : '';
                        @endphp
                        <option value="{{ $val }}" {{ $selected }}>
                            {{ ucfirst($val) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>        
    </div>

    <!-- Properties -->
    <div class="col-6">
        <h3 class="form-group">{{ $is_pi ? 'PI Properties' : trans('quotes.properties')}}</h3>
        <div class="form-group row">
            <div class="col-4">
                <label for="reference" >Djc Reference</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                    {{ Form::text('reference', null, ['class' => 'form-control round', 'placeholder' => 'Djc Reference', 'id' => 'reference']) }}
                </div>
            </div>
            <div class="col-4">
                <label for="reference_date" >Djc Reference Date</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                    {{ Form::text('reference_date', null, ['class' => 'form-control round datepicker', 'id' => 'referencedate']) }}
                </div>
            </div>
            <div class="col-4">
                <label for="date">{{trans('general.date')}}</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                    {{ Form::text('date', null, ['class' => 'form-control round datepicker', 'id' => 'date']) }}
                </div>
            </div>                                    
        </div>

        <div class="form-group row">
            <div class="col-4"><label for="validity" >Validity Period</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                    <select class="custom-select round" name="validity" id="validity">
                        @php
                            $selected = '';
                        @endphp
                        @foreach ([0, 14, 30, 45, 60, 90] as $val)
                            @php
                                if (isset($quote)) $selected =  $val == @$quote->validity? 'selected' : '';
                                else $selected = $val == 0? 'selected' : '';
                            @endphp
                            <option value="{{ $val }}" {{ $selected }}>
                                {{ $val ? 'Valid For '.$val.' Days' : 'On Receipt' }}
                            </option>
                        @endforeach                                                
                    </select>
                </div>
            </div>
            <div class="col-4">
                <label for="currency" >Currency</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                    <select class="custom-select" name="currency_id" id="currency" data-placeholder="{{trans('tasks.assign')}}" required>
                        @foreach ($currencies as $key => $currency)
                            @php 
                                $selected = '';
                                if ($currency->id == @$quote->currency_id) $selected = 'selected';
                                elseif ($currency->id == 1 && !@$quote) $selected = 'selected';
                                $rate_label = $currency->rate > 1? "1/" . (+$currency->rate) : '';
                            @endphp
                            <option 
                                value="{{ $currency->id }}" 
                                currency_rate="{{ +$currency->rate }}" 
                                {{ $selected }}
                            >
                                {{ $currency->code }} {{ $rate_label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-4"><label for="client_ref" >Client Ref / Callout ID</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                    {{ Form::text('client_ref', null, ['class' => 'form-control round', 'id' => 'client_ref']) }}
                </div>
            </div>                                                                          
        </div>
        <div class="form-group row">
            <div class="col-4">
                <label for="terms">Terms</label>
                <select id="term_id" name="term_id" class="custom-select" required>
                    <option value="">-- Select Term --</option>
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}" {{ $term->id == @$quote->term_id ? 'selected' : '' }}>
                            {{ $term->title }}
                        </option>
                    @endforeach
                </select>               
            </div>
            <div class="col-4">
                <label for="taxFormat">Tax</label>
                <select class="custom-select" name='tax_id' id="tax_id">
                    @foreach ($additionals as $row)
                        <option value="{{ +$row->value }}" {{ @$quote && round($row->value) == @$quote->tax_id ? 'selected' : '' }}>
                            {{ $row->name }}
                        </option>
                    @endforeach                                            
                </select>
                <input type="hidden" name="tax_format" value="exclusive" id="tax_format">
            </div>
            @if (isset($banks))
                <div class="col-4">
                    <label for="bank" >Bank</label>
                    <select class="custom-select" name='bank_id' id="bank_id" required>
                        <option value="">-- Select Bank --</option>
                        @foreach ($banks as $bank)
                        <option value="{{ $bank->id }}" {{ $bank->id == @$quote->bank_id ? 'selected' : '' }}>
                            {{ $bank->bank }} {{ $bank->note? "- {$bank->note}" : '' }}
                        </option>
                        @endforeach                                            
                    </select>
                </div>
            @endif
        </div>
    </div>                        
</div>
<div class="form-group row">
    @if (isset($revisions))
        <div class="col-10">
            <label for="subject" >Subject / Title</label>
            {{ Form::text('notes', null, ['class' => 'form-control', 'id' => 'subject', 'required']) }}
        </div>
        <div class="col-2">
            <label for="revision" >Revision</label>
            <select class="custom-select" name='revision' id="rev">
                <option value="">-- Select Revision --</option>
                @foreach ($revisions as $val)
                    <option value="_r{{ $val }}" {{ @$quote->revision == '_r'.$val ? 'selected' : '' }}>
                        R{{ $val }}
                    </option>
                @endforeach                                            
            </select>
        </div>
    @else
        <div class="col-12">
            <label for="subject" >Subject / Title</label>
            {{ Form::text('notes', null, ['class' => 'form-control', 'id' => 'subject', 'placeholder' => 'Subject or Title', 'required']) }}
        </div>
    @endif
</div>
<!-- quotes item table -->
@include('focus.quotes.partials.quote_items')
<!-- footer -->
<div class="form-group row">
    <div class="col-9">
        <a href="javascript:" class="btn btn-success" id="addProduct"><i class="fa fa-plus-square"></i> Add Product</a>
        <a href="javascript:" class="btn btn-primary" id="addTitle"><i class="fa fa-plus-square"></i> Add Title</a>
        <a href="javascript:" class="btn btn-secondary ml-1 d-none" data-toggle="modal" data-target="#skillModal" id="addSkill">
            <i class="fa fa-wrench"></i> Labour
        </a>
        <a href="javascript:" class="btn btn-warning" id="addMisc"><i class="fa fa-plus"></i> Expense & Misc</a>
        <a href="javascript:" class="btn btn-purple ml-1" data-toggle="modal" data-target="#extrasModal" id="addExtras">
            <i class="fa fa-plus"></i> Header & Footer
        </a>
    </div>
    <div class="col-3">
        <div>
            <label><span class="text-primary">(Estimated Cost: <span class="estimate-cost font-weight-bold text-dark">0.00</span>)</span></label>
        </div>
        <div>
            <label class="m-0">Taxable Amount</label>
            <input type="text" name="taxable" id="taxable" class="form-control" readonly>    
        </div>
        <div>
            <label class="m-0">SubTotal</label>
            <input type="text" name="subtotal" id="subtotal" class="form-control" readonly>    
        </div>
        <div>
            <label id="tax-label" class="m-0">
                {{ trans('general.total_tax') }}
                <span id="vatText" class="text-primary">(Print VAT-Exc)</span>
            </label>
            <input type="text" name="tax" id="tax" class="form-control" readonly>
        </div>
        <div>
            <label class="m-0">
                {{trans('general.grand_total')}}
                <b class="text-primary">(E.P: &nbsp;<span class="text-dark profit">0</span>)</b>
            </label>
            <input type="text" name="total" class="form-control" id="total" readonly>
        </div>
        {{ Form::submit('Generate', ['class' => 'btn btn-success btn-lg mt-1']) }}
    </div>
</div>
<!-- repair or maintenance type  -->
@if (request('doc_type') == 'maintenance') 
    {{ Form::hidden('is_repair', 0) }}
@endif
@include('focus.quotes.partials.skillset-modal')
@include('focus.quotes.partials.extras_modal')