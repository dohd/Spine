<div class="row">
    @if ($quote->status == 'canceled')
        <div class="badge text-center white d-block m-1">
            <span class="bg-danger round p-1">
                &nbsp;&nbsp;{{trans('payments.'.$quote['status'])}}&nbsp;&nbsp;
            </span>
        </div>
    @else
        <div class="col">
            <a href="{{ route('biller.quotes.edit', $quote) }}" class="btn btn-warning mb-1"><i class="fa fa-pencil"></i> Edit</a>
            @if (access()->allow('quote-delete'))
                <a class="btn btn-danger mb-1 quote-delete" href="javascript:void(0);"><i class="fa fa-trash"></i> Delete
                    {{ Form::open(['url' => route('biller.quotes.destroy', $quote), 'method' => 'delete']) }} {{ Form::close() }}               
                </a>
            @endif

            @php
                $valid_token = token_validator('', 'q'.$quote->id . $quote->tid, true);
            @endphp
            <div class="btn-group ">
                @if ($quote->status == 'approved')
                    <button type="button" class="btn btn-success mb-1 btn-min-width dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-repeat"></i> Verify & Download
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="{{ route('biller.quotes.verify', $quote->id) }}">Verify</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('biller.print_bill', [$quote->id, 4, $valid_token, 2]) }}">{{trans('general.pdf')}}</a>
                    </div>
                @else
                    <button type="button" class="btn btn-success mb-1 btn-min-width" disabled><i class="fa fa-repeat"></i> Verify & Download</button>
                @endif                
            </div>                                
            <a href="#pop_model_1" data-toggle="modal" data-remote="false" class="btn btn-large btn-blue mb-1" title="Change Status">
                <span class="fa fa-check"></span> {{trans('general.change_status')}}
            </a>
            <a href="#pop_model_4" data-toggle="modal" data-remote="false" class="btn btn-large btn-cyan mb-1" title="Add LPO">
                <span class="fa fa-retweet"></span> Add LPO
            </a>

            <div style="display:inline-block; margin-left:5%">
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
    @endif
</div>
