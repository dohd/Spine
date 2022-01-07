@extends ('core.layouts.app')

@section ('title', 'Stock Issuance Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h4 class="content-header-title">Stock Issuance Management</h4>
        </div>   

        <div class="content-header-right col-md-6 col-12">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    <a href="{{ route('biller.stockissuance.index') }}" class="btn btn-primary">
                        <i class="ft-list"></i> List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-body">
                {{ Form::model($quote, ['route' => ['biller.stockissuance.store', $quote], 'method' => 'POST' ]) }}
                <input type="hidden" name="quote_id" value="{{ $quote->id }}">
                <div class="form-group row">
                    <div class="col-12">
                        @php
                            $title = $quote->bank_id ? 'Proforma Invoice' : 'Quote';
                        @endphp
                        <h3 class="title">{{ $title }}</h3>                                        
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-12">
                        <label for="subject" class="caption">Subject / Title</label>
                        {{ Form::text('notes', null, ['class' => 'form-control', 'id'=>'subject', 'disabled']) }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 cmp-pnl">
                        <div id="customerpanel" class="inner-cmp-pnl">                        
                            <div class="form-group row">                                  
                                <div class="col-4">
                                    <label for="invoiceno" class="caption">
                                        @if ($quote->bank_id)
                                            #PI {{ trans('general.serial_no') }}
                                        @else
                                            #QT {{ trans('general.serial_no') }}
                                        @endif
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span></div>
                                        @php
                                            $tid = sprintf('%04d', $quote->tid);
                                            $tid = $quote->bank_id ? 'PI-'.$tid : 'QT-'.$tid;                                             
                                        @endphp
                                        {{ Form::text('tid', $tid, ['class' => 'form-control round', 'disabled']) }}
                                    </div>
                                </div>
                                <div class="col-4"><label for="invoicedate" class="caption">Quote {{trans('general.date')}}</label>
                                    <div class="input-group">
                                        <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                                        {{ Form::text('invoicedate', null, ['class' => 'form-control round', 'data-toggle' => 'datepicker-qd', 'disabled']) }}
                                    </div>
                                </div>                                                                
                                <div class="col-4"><label for="client_ref" class="caption">Client Reference / Callout ID</label>
                                    <div class="input-group">
                                        <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                                        {{ Form::text('client_ref', null, ['class' => 'form-control round', 'id' => 'client_ref', 'disabled']) }}
                                    </div>
                                </div> 
                            </div> 
                        </div>
                    </div>
                </div> 

                <div>
                    <ul class="nav nav-tabs nav-top-border no-hover-bg nav-justified" role="tablist">
                        {{-- Hide budget items 
                        <li class="nav-item">
                            <a class="nav-link active" id="active-tab1" data-toggle="tab" href="#active1" aria-controls="active1" role="tab" aria-selected="true">
                                Budget Items
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link " id="active-tab2" data-toggle="tab" href="#active2" aria-controls="active2" role="tab">
                                Issue Items
                            </a>
                        </li>    
                        --}}                    
                    </ul>
                    

                    <div class="tab-content px-1 pt-1">
                        {{-- Hide budget table
                        <div class="tab-pane active in" id="active1" aria-labelledby="tab1" role="tabpanel">
                            <table id="budget-item" class="table-responsive tfr my_stripe_single mb-1">
                                <thead>
                                    <tr class="item_header bg-gradient-directional-blue white">
                                        <th width="39%" class="text-center">{{trans('general.item_name')}}</th>
                                        <th width="7%" class="text-center">UOM</th>
                                        <th width="8%" class="text-center">{{trans('general.quantity')}}</th> 
                                        <th width="8%" class="text-center">New Quantity</th>     
                                        <th width="16%" class="text-center">Price (VAT Exc)</th>
                                        <th width="16%" class="text-center">Amount</th>                             
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>                                                       
                        </div>
                        --}}

                        <div class="tab-pane active in" id="active2" aria-labelledby="tab2" role="tabpanel">
                            <table id="issue-item" class="table-responsive tfr my_stripe_single mb-1">
                                <thead>
                                    <tr class="item_header bg-gradient-directional-blue white">
                                        <th width="40%" class="text-center">{{trans('general.item_name')}}</th>
                                        <th width="8%" class="text-center">UOM</th>
                                        <th width="8%" class="text-center">{{trans('general.quantity')}}</th> 
                                        <th width="8%" class="text-center">Issue Quantity</th>     
                                        <th width="16%" class="text-center">Price (VAT Exc)</th>
                                        <th width="16%" class="text-center">Action</th>                            
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            <div class="row mb-1">
                                <div class="col-10 payment-method last-item-row sub_c">
                                    <button type="button" class="btn btn-success" id="add-product">
                                        <i class="fa fa-plus-square"></i> Add Item
                                    </button>
                                </div>
                                <div class="col-2 mt-2">                                    
                                    @if (count($issued_items))
                                        {{ Form::submit('Issue Stock', ['class' => 'btn btn-success btn-lg', 'disabled']) }}   
                                    @else                                        
                                        {{ Form::submit('Issue Stock', ['class' => 'btn btn-success btn-lg']) }}
                                    @endif                                    
                                </div>                            
                            </div>
                        </div>                       
                    </div>
                </div>
                {{ Form::close() }}   
            </div>
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
<script>
    // ajax setup
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // initialize Quote Date datepicker
    $('.datepicker')
        .datepicker({ format: "{{ config('core.user_date_format') }}" })
        .datepicker('setDate', new Date("{{ $quote->invoicedate }}"));
    
    // product row
    function productRow(n) {
        return `
            <tr>
                <td><input type="text" class="form-control" name="product_name[]" id="itemname-${n}" required></td>
                <td><input type="text" class="form-control" name="unit[]" id="unit-${n}" required></td>          
                <td><input type="number" class="form-control update" name="new_qty[]" id="newqty-${n}" readonly></td>
                <td><input type="number" class="form-control" name="issue_qty[]" id="issueqty-${n}" required></td>
                <td><input type="text" class="form-control update" name="price[]" id="price-${n}" required></td>
                <td class="text-center"><button type="button" class="btn btn-primary removeItem">Remove</button></td>
                <input type="hidden" name="product_id[]" value="0" id="productid-${n}">
                <input type="hidden" name="item_id[]" value="0" id="itemid-${n}">
            </tr>
        `;
    }

    function assignVal(i, v) {
        $('#issue-item tbody').append(productRow(i));
        // set default values
        $('#itemid-'+i).val(v.id);
        $('#productid-'+i).val(v.product_id);
        $('#itemname-'+i).val(v.product_name);
        $('#unit-'+i).val(v.unit);                
        $('#newqty-'+i).val(v.new_qty);
        $('#price-'+i).val(parseFloat(v.price).toLocaleString());
        if (v.issue_qty) $('#issueqty-'+i).val(v.issue_qty);
    }

    // set default product rows
    const budgetItems = @json($budget->budget_items);
    const issuedItems = @json($issued_items);
    let productIndx = 0;
    if (issuedItems.length) {
        issuedItems.forEach(v => {
            assignVal(productIndx, v);                  
            productIndx++;
        });
        $('.removeItem').attr('disabled', true);
        $('#add-product').attr('disabled', true);
    } else {
        budgetItems.forEach(v => {
            assignVal(productIndx, v);        
            productIndx++;
        });
    }

    // add product row
    $('#add-product').click(function() {
        const i = productIndx;
        $('#issue-item tbody').append(productRow(i));
        $('#itemname-'+i).autocomplete(autocompleteProp(i));
        productIndx++;
    });

    $('#issue-item').on('click', '.removeItem', function() {
        const itemId = $(this).parent().next('input[type=hidden]').val();
        $(this).closest('tr').remove();
    });

    // autocompleteProp returns autocomplete object properties
    function autocompleteProp(i) {
        return {
            source: function(request, response) {
                $.ajax({
                    url: baseurl + 'products/quotesearch/'+i,
                    dataType: "json",
                    method: 'post',
                    success: function(data) {
                        response(data.map(v => ({
                            label: v.name,
                            value: v.name,
                            data: v
                        })));
                    }
                });
            },
            autoFocus: true,
            minLength: 0,
            select: function(event, ui) {
                const {data} = ui.item;
                $('#productid-'+i).val(data.id);
                $('#itemname-'+i).val(data.name);
                $('#unit-'+i).val(data.unit);                

                const price = parseFloat(data.purchase_price.replace(/,/g, ''));
                $('#price-'+i).val(price.toLocaleString());
            }
        };
    }    
</script>
@endsection