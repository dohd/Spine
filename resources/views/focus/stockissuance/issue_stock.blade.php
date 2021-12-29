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
                {{ Form::model($quote, ['route' => ['biller.projects.quote_budget', $quote], 'method' => 'PATCH' ]) }}
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
                        {{-- Hide expense tab 
                        <li class="nav-item">
                            <a class="nav-link " id="active-tab3" data-toggle="tab" href="#active3" aria-controls="active3" role="tab">
                                Expense
                            </a>
                        </li>
                        --}}
                    </ul>

                    <div class="tab-content px-1 pt-1">
                        <div class="tab-pane active in" id="active1" aria-labelledby="tab1" role="tabpanel">
                            <table id="quotation" class="table-responsive tfr my_stripe_single mb-1">
                                <thead>
                                    <tr class="item_header bg-gradient-directional-blue white">
                                        <th width="39%" class="text-center">{{trans('general.item_name')}}</th>
                                        <th width="7%" class="text-center">UOM</th>
                                        <th width="8%" class="text-center">{{trans('general.quantity')}}</th> 
                                        <th width="8%" class="text-center">Request Quantity</th>     
                                        <th width="16%" class="text-center">Price (VAT Exc)</th>
                                        <th width="16%" class="text-center">Amount</th>                             
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>                                                       
                        </div>

                        <div class="tab-pane" id="active2" aria-labelledby="tab2" role="tabpanel">
                            <table id="quotation" class="table-responsive tfr my_stripe_single mb-1">
                                <thead>
                                    <tr class="item_header bg-gradient-directional-blue white">
                                        <th width="39%" class="text-center">{{trans('general.item_name')}}</th>
                                        <th width="7%" class="text-center">UOM</th>
                                        <th width="8%" class="text-center">{{trans('general.quantity')}}</th> 
                                        <th width="8%" class="text-center">Issue Quantity</th>     
                                        <th width="16%" class="text-center">Price (VAT Exc)</th>
                                        <th width="16%" class="text-center">Amount</th>                             
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            <div class="row mb-1">
                                <div class="col-12 payment-method last-item-row sub_c">
                                    <button type="button" class="btn btn-success" id="add-product">
                                        <i class="fa fa-plus-square"></i> Add Item
                                    </button>
                                </div>                            
                            </div>   
                        </div>
                        
                        {{-- Hide expense tab content 
                        <div class="tab-pane" id="active3" aria-labelledby="tab3" role="tabpanel">
                            <table id="quotation" class="table-responsive tfr my_stripe_single mb-1">
                                <thead>
                                    <tr class="item_header bg-gradient-directional-blue white">
                                        <th width="30%" class="text-center">Supplier</th>
                                        <th width="20%" class="text-center">Item Description</th>
                                        <th width="8%" class="text-center">Amount</th> 
                                        <th width="10%" class="text-center">Transaction ID</th>     
                                        <th width="16%" class="text-center">Document Type</th>
                                        <th width="16%" class="text-center">Reference No</th>                             
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            <div class="row mb-1">
                                <div class="col-12 payment-method last-item-row sub_c">
                                    <button type="button" class="btn btn-success" id="add-product">
                                        <i class="fa fa-plus-square"></i> Add Item
                                    </button>
                                </div>                            
                            </div>  
                        </div>
                        --}}
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

</script>
@endsection