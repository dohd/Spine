@php
    $query_str = request()->getQueryString();
    $edit_url = $query_str ? route('biller.quotes.edit', [$quote, $query_str]) : route('biller.quotes.edit', $quote);
@endphp
<div class="row">
    <div class="col">
        <a href="{{ $edit_url }}" class="btn btn-warning mb-1"><i class="fa fa-pencil"></i> Edit</a>
        @if ($quote->bank_id)
            <a href="{{ route('biller.quotes.edit', [$quote, 'page=copy_to_qt']) }}" class="btn btn-cyan mb-1"><i class="fa fa-clone" aria-hidden="true"></i></i> Quote Copy</a>
        @else
            <a href="{{ route('biller.quotes.edit', [$quote, 'page=copy_to_pi']) }}" class="btn btn-cyan mb-1"><i class="fa fa-clone" aria-hidden="true"></i></i> PI Copy</a>
        @endif

        @if (access()->allow('quote-delete'))
            <a class="btn btn-danger mb-1 quote-delete" href="javascript:void(0);">
                <i class="fa fa-trash"></i> Delete
                {{ Form::open(['route' => ['biller.quotes.destroy', $quote], 'method' => 'delete']) }} {{ Form::close() }}               
            </a>
        @endif

        @php
            $valid_token = token_validator('', 'q'.$quote->id . $quote->tid, true);
        @endphp
        
        @if ($quote->status == 'approved')
            <div class="btn-group ">
                <button type="button" class="btn btn-success mb-1 btn-min-width dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-repeat"></i> Verify & Download
                </button>
                <div class="dropdown-menu">
                    @if ($query_str)
                        <a class="dropdown-item" href="{{ route('biller.quotes.verify', [$quote, $query_str]) }}">Verify</a>
                    @else
                        <a class="dropdown-item" href="{{ route('biller.quotes.verify', $quote) }}">Verify</a>
                    @endif
                    @if ($quote->verified == 'Yes')
                        <hr>
                        <a class="dropdown-item" href="{{ route('biller.print_quote', [$quote->id, 4, $valid_token, 1, 'verified=Yes']) }}" target="_blank">
                            {{trans('general.pdf')}}
                        </a>
                    @endif
                </div>                                  
            </div>                                
        @endif 
        <div class="btn-group">
            <button type="button" class="btn btn-large btn-blue mb-1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fa fa-check"></i> {{trans('general.change_status')}}
            </button>
            <div class="dropdown-menu">
                <a href="#pop_model_1" data-toggle="modal" data-remote="false" class="dropdown-item" title="Change Status">
                    Approve
                </a>
                <a href="javascript:void(0);" class="dropdown-item text-danger quote-cancel">
                    <i class="fa fa-ban" aria-hidden="true"></i> Cancel
                    {{ Form::open(['route' => ['biller.quotes.cancel', $quote], 'method' => 'patch']) }} {{ Form::close() }}  
                </a>
            </div>
        </div>

        <a href="#pop_model_4" data-toggle="modal" data-remote="false" class="btn btn-large btn-cyan mb-1" title="Add LPO">
            <span class="fa fa-retweet"></span> Add LPO
        </a>

        <div  class="d-inline-block ml-5">
            <div class="btn-group">
                <button type="button" class="btn btn-facebook dropdown-toggle mb-1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="fa fa-envelope-o"></span> {{trans('customers.email')}}
                </button>
                <div class="dropdown-menu">
                    <a href="#sendEmail" data-toggle="modal" data-remote="false" class="dropdown-item send_bill" data-type="6" data-type1="proposal">
                        {{trans('general.quote_proposal')}}
                    </a>
                </div>
            </div>
            <!-- SMS -->
            <div class="btn-group">
                <button type="button" class="btn btn-blue dropdown-toggle mb-1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="fa fa-mobile"></span> {{trans('general.sms')}}
                </button>
                <div class="dropdown-menu">
                    <a href="#sendSMS" data-toggle="modal" data-remote="false" class="dropdown-item send_sms" data-type="16" data-type1="proposal">
                        {{trans('general.quote_proposal')}}                            
                    </a>
                </div>
            </div>
            
            <a href="{{ route('biller.view_bill', [$quote->id, 4, $valid_token, 0]) }}" class="btn btn-blue-grey mb-1">
                <i class="fa fa-globe"></i> {{trans('general.preview')}}
            </a>
        </div>
    </div>
</div>
