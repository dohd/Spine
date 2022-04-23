<div class="row">
    <!-- Quote -->
    <div class="col-6">
        <h3 class="form-group">{{ $words['title'] }}</h3>
        <div class="form-group row">
            <div class="col-12">
                <label for="ticket" >Search Ticket</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                    <select class="form-control" name="lead_id" id="lead_id" required>                                                 
                        <option value="">-- Select Ticket --</option>
                        @foreach ($leads as $lead)
                            @php
                                $tid = 'Tkt-'.sprintf('%04d', $lead->reference);
                                $name =  isset($lead->customer) ? $lead->customer->company : $lead->client_name;
                                $branch = isset($lead->branch) ? $lead->branch->name : '';
                                if ($name && $branch) $name .= ' - ' . $branch;  
                            @endphp
                            <option 
                                value="{{ $lead->id }}" 
                                {{ $lead->id == @$quote->lead_id ? 'selected' : '' }}
                                title="{{ $lead->title }}" 
                                client_ref="{{ $lead->client_ref }}"
                                customer_id="{{ $lead->client_id }}"
                                branch_id="{{ $lead->branch_id }}"
                            >
                                {{ $tid }} - {{ $name }} - {{ $lead->title }}
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
                <label for="pricing" >Pricing</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                    <select id="pricegroup_id" name="pricegroup_id" class="form-control round">
                        <option value="0" selected>Default </option>
                        @foreach($selling_prices as $price)
                            <option value="{{$price->id}}">{{$price->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-3">
                <label for="serial_no" >{{trans('general.serial_no')}} </label>
                <div class="input-group">
                    <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span></div>
                    @php
                        $label = $is_pi ? 'PI-' : 'QT-';
                        $tid = sprintf('%04d', $lastquote->tid);
                        if (!isset($words['edit_mode'])) $tid = sprintf('%04d', $lastquote->tid+1);
                        $label .= $tid;
                    @endphp
                    {{ Form::text('tid', $label, ['class' => 'form-control round', 'id' => 'tid', 'disabled']) }}
                    <input type="hidden" name="tid" value="{{ $tid }}">
                </div>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-6">
                <label for="attention" >Attention</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                    {{ Form::text('attention', null, ['class' => 'form-control round', 'placeholder' => 'Attention', 'id'=>'attention', 'required']) }}
                </div>
            </div>
            <div class="col-6">                
                <label for="prepared_by" > Prepared By</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                    {{ Form::text('prepared_by', null, ['class' => 'form-control round', 'placeholder' => 'Prepaired By', 'id'=>'prepared_by', 'required']) }}
                </div>
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
                    {{ Form::text('reference', null, ['class' => 'form-control round', 'placeholder' => 'Djc Reference', 'id' => 'reference', 'required']) }}
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
                <label for="invoicedate" >Quote {{trans('general.date')}}</label>
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
                    <select class="form-control round" name="validity" id="validity">
                        @foreach ([0, 14, 30, 45, 60, 90] as $val)
                            <option value="{{ $val }}" {{ !$val ? 'selected' : '' }}>
                                {{ $val ? 'Valid For '.$val.' Days' : 'On Receipt' }}
                            </option>
                        @endforeach                                                
                    </select>
                </div>
            </div>
            <div class="col-4">
                <label for="currency" >Currency <span class="text-danger">*</span></label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                    <select class="form-control" name="currency_id" id="currency" data-placeholder="{{trans('tasks.assign')}}" required>
                        @foreach($currencies as $curr)
                            <option value="{{ $curr->id }}" {{ $curr->id === 1? 'selected' : '' }}>
                                {{ $curr->symbol }} - {{ $curr->code }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-4"><label for="client_ref" >Client Ref / Callout ID</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                    {{ Form::text('client_ref', null, ['class' => 'form-control round', 'id' => 'client_ref', 'required']) }}
                </div>
            </div>                                                                          
        </div>
        <div class="form-group row">
            <div class="col-4">
                <label for="source" >Quotation Terms <span class="text-danger">*</span></label>
                <select id="term_id" name="term_id" class="form-control" required>
                    <option value="">-- Select Term --</option>
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}" {{ $term->id == @$quote->term_id ? 'selected' : '' }}>
                            {{ $term->title }}
                        </option>
                    @endforeach
                </select>               
            </div>
            <div class="col-4">
                <label for="taxFormat" >{{trans('general.tax')}}</label>
                <select class="form-control" name='tax_id' id="tax_id">
                    @foreach ([16, 8, 0] as $val)
                    <option value="{{ $val }}" {{ $val == 16 ? 'selected' : '' }}>
                        {{ $val ? $val .'% VAT' : 'Off' }}
                    </option>
                    @endforeach                                            
                </select>
                <input type="hidden" name="tax_format" value="exclusive" id="tax_format">
            </div>
            @if (isset($banks))
                <div class="col-4">
                    <label for="bank" >Bank</label>
                    <select class="form-control" name='bank_id' id="bank_id" required>
                        <option value="">-- Select Bank --</option>
                        @foreach ($banks as $bank)
                        <option value="{{ $bank->id }}" {{ $bank->id == @$quote->bank_id ? 'selected' : '' }}>
                            {{ $bank->bank }}
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
            <select class="form-control" name='revision' id="rev" required>
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
@include('focus.quotes.partials.quote-items-table')

<!-- footer -->
<div class="form-group row">
    <div class="col-9">
        <button type="button" class="btn btn-success" aria-label="Left Align" id="addProduct">
            <i class="fa fa-plus-square"></i> Add Product
        </button>
        <button type="button" class="btn btn-primary" aria-label="Left Align" id="addTitle">
            <i class="fa fa-plus-square"></i> Add Title
        </button>
        <button type="button" class="btn btn-secondary ml-1" data-toggle="modal" data-target="#skillModal" id="addSkill">
            <i class="fa fa-plus-square"></i> Add Skillset
        </button>
    </div>

    <div class="col-3">
        <label>SubTotal ({{ config('currency.symbol') }})</label>
        <div class="input-group">
            <input type="text" name="subtotal" id="subtotal" class="form-control" readonly>
        </div>
        <label id="tax-label">{{ trans('general.total_tax') }} ({{ config('currency.symbol') }})</label>
        <div class="input-group">
            <input type="text" name="tax" id="tax" class="form-control" readonly>
        </div>
        <label>
            {{trans('general.grand_total')}} ({{ config('currency.symbol') }})
            <b class="text-primary">
                (Profit: &nbsp;<span class="text-dark profit">0</span>)
            </b>
        </label>
        <div class="input-group">
            <input type="text" name="total" class="form-control" id="total" readonly>
        </div>
        {{ Form::submit('Generate', ['class' => 'btn btn-success btn-lg mt-1']) }}
    </div>
</div>

<!-- skillset modal -->
@include('focus.quotes.partials.skillset-modal')