@php 
    $invoice=$quote; 
@endphp

<div class="row">
    @if ($quote['status'] != 'canceled')
        <div class="col">
            <a href="{{$quote['id']}}/edit" class="btn btn-warning mb-1"><i class="fa fa-pencil"></i> {{trans('labels.backend.quotes.edit')}}</a>
            <a href="{{$quote['id']}}/copy" class="btn btn-large btn-cyan mb-1"><i class="fa fa-clone" aria-hidden="true"></i> Copy </a>
            @php
                $qt_link = route('biller.quotes.verify',[$quote->id ]);
                $valid_token = token_validator('', 'q' . $quote['id'].$quote['tid'], true);
                $link = route('biller.print_bill', [$quote['id'], 4, $valid_token, 1]);
                $link_download = route('biller.print_bill', [$quote['id'], 4, $valid_token, 2]);
                $link_preview = route('biller.view_bill', [$quote['id'], 4, $valid_token, 0]);
            @endphp
            <div class="btn-group ">
                <button type="button" class="btn btn-success mb-1 btn-min-width dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-repeat"></i> Verify & Download
                </button>                                    
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="{{$qt_link}}">Verify</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{$link_download}}">{{trans('general.pdf')}}</a>
                </div>
            </div>                                
            <div class="btn-group">
                <button type="button" class="btn btn-facebook dropdown-toggle mb-1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="fa fa-envelope-o"></span> {{trans('customers.email')}}
                </button>
                <div class="dropdown-menu"><a href="#sendEmail" data-toggle="modal" data-remote="false" class="dropdown-item send_bill" data-type="6" data-type1="proposal">{{trans('general.quote_proposal')}}</a>
                </div>
            </div>
            <!-- SMS -->
            <div class="btn-group">
                <button type="button" class="btn btn-blue dropdown-toggle mb-1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="fa fa-mobile"></span> {{trans('general.sms')}}
                </button>
                <div class="dropdown-menu"><a href="#sendSMS" data-toggle="modal" data-remote="false" class="dropdown-item send_sms" data-type="16" data-type1="proposal">{{trans('general.quote_proposal')}}</a>
                </div>
            </div>
            
            <div class="btn-group ">
                <button type="button" class="btn btn-success mb-1 btn-min-width dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-print"></i> {{trans('general.print')}}
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="{{$link}}">{{trans('general.print')}}</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{$link_download}}">{{trans('general.pdf')}}</a>
                </div>
            </div>
            <a href="{{$link_preview}}" class="btn btn-blue-grey mb-1"><i class="fa fa-globe">
                </i> {{trans('general.preview')}}
            </a>
            <a href="#pop_model_1" data-toggle="modal" data-remote="false" class="btn btn-large btn-danger mb-1" title="Change Status">
                <span class="fa fa-check"></span> {{trans('general.change_status')}}
            </a>
            <a href="#pop_model_4" data-toggle="modal" data-remote="false" class="btn btn-large btn-cyan mb-1" title="Add LPO">
                <span class="fa fa-retweet"></span> Add LPO
            </a>
        </div>
    @else
        <div class="badge text-center white d-block m-1">
            <span class="bg-danger round p-1">
                &nbsp;&nbsp;{{trans('payments.'.$quote['status'])}}&nbsp;&nbsp;
            </span>
        </div>
    @endif
</div>

@include("focus.modal.quote_status_model")
@include('focus.modal.email_model', array('category'=>4))
@include('focus.modal.sms_model', array('category'=>4))
@include("focus.modal.cancel_model")
@include("focus.modal.convert_model")
@include("focus.modal.lpo_model")
