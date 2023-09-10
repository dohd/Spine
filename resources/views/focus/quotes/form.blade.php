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
                <label for="ticket">File Info</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                    <select class="form-control" name="lead_id" id="lead_id" data-placeholder="Search by File No, Subject, Client, Branch" required> 
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
            <div class="col-4">
                <label for="quote_type">Quote Type</label>
                <select name="quote_type" id="quote_type" class="custom-select" required>
                    @foreach (['HOTEL BOOKING','SAFARI PACKAGE', 'FLIGHT'] as $value)
                        <option value="{{ $value }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-4">
                <label for="attention">Attention</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                    {{ Form::text('attention', null, ['class' => 'form-control round', 'placeholder' => 'Attention', 'id' => 'attention']) }}
                </div>
            </div>
            
            <div class="col-4">
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
        </div>
    </div>

    <!-- Properties -->
    <div class="col-6">
        <h3 class="form-group">{{ $is_pi ? 'PI Properties' : trans('quotes.properties')}}</h3>
        <div class="form-group row">
            <div class="col-4">
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

            <div class="col-4">
                <label for="date">{{trans('general.date')}}</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                    {{ Form::text('date', null, ['class' => 'form-control round datepicker', 'id' => 'date']) }}
                </div>
            </div>    
            
            <div class="col-4">
                <label for="quote_type">Has Attachable Docs ?</label>
                <select name="quote_type" id="quote_type" class="custom-select" required>
                    @php $selected = ''; @endphp
                    @foreach (['standard' => 'No', 'project' => 'Yes'] as $key => $value)
                        @php
                            if (isset($quote)) $selected = ($quote->quote_type == $key)? 'selected' : '';
                            else $selected = $key? 'selected' : '';
                        @endphp
                        <option value="{{ $key }}" {{ $selected }}>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-4"><label for="validity" >Validity Period</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                    <select class="custom-select round" name="validity" id="validity">
                        @php $selected = ''; @endphp
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

            <div class="col-4">
                <label for="currency" >Currency</label>
                <select class="custom-select currency" name="currency_id[]">
                    @foreach (['KES', 'USD'] as $value)
                        <option value="{{ $value }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>                        
</div>

<div class="form-group row">
    @if (isset($revisions))
        <div class="col-10">
            <label for="subject">Title (Subject)</label>
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
            <label for="subject">Title (Subject)</label>
            {{ Form::text('notes', null, ['class' => 'form-control', 'id' => 'subject', 'placeholder' => 'Title (Subject)', 'required']) }}
        </div>
    @endif
</div>

<!-- Quotes Item Table -->
<div class="mt-3" id="table_container"></div>
<!-- End Quote Items -->

<div class="form-group row">
    <div class="col-9">
        <a href="javascript:" class="btn btn-success" id="add_product"><i class="fa fa-plus-square"></i> Add Product</a>        
        <a href="javascript:" class="btn btn-purple ml-1" data-toggle="modal" data-target="#extrasModal" id="addExtras">
            <i class="fa fa-plus"></i> Header & Footer
        </a>
    </div>
</div>

<div class="form-group row">
    <div class="col-2 ml-auto">
        <label for="total">Totals</label>
        {{ Form::text('total', null, ['class' => 'form-control', 'id' => 'total', 'placeholder' => '0.00', 'readonly']) }}
    </div>
</div>

@include('focus.quotes.partials.extras_modal')

@section("after-scripts")
@include('focus.quotes.form_js')
@endsection
