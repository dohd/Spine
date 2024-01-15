@extends ('core.layouts.app')

@section('title', 'Client Tickets Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Client Tickets Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.client_vendor_tickets.partials.tickets-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        @if ($client_vendor_ticket->status == 'Closed')
            <div class="badge text-center white d-block">
                <h5><span class="btn btn-warning round text-white"><b>Closed Ticket! Reply to the ticket to reopen it.</b></span></h5>
            </div>
        @endif
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <a href="#" class="btn btn-danger float-right" id="close"><i class="fa fa-times" aria-hidden="true"></i> Closed</a>
                    {{ Form::open(['route' => ['biller.tenant_tickets.status', $client_vendor_ticket], 'method' => 'PATCH', 'id' => 'closeForm']) }} {{ Form::close() }}
                    <button type="button" class="btn btn-outline-secondary float-right mr-1" id="reply" onclick="window.scrollTo(0, document.body.scrollHeight)"><i class="fa fa-pencil" aria-hidden="true"></i> Reply</button>                    
                    <h3 class="text-success mb-1">{{ gen4tid('#TKT-', $client_vendor_ticket->tid) }}</h3>
                    <h5>Subject: <b>{{ $client_vendor_ticket->subject }}</b></h5>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    @foreach ($client_vendor_ticket->replies as $reply)
                        <div>
                            <h5 class="float-right"><span class="badge badge-info">{{ 'Operator' }}</span></h5>
                            <h5>Posted By <b>{{ @$reply->user->name }}</b></h5>
                            <h6 class="text-light">{{ date('D j, F, Y', strtotime($reply->date)) }}</h6>
                            <br>
                            <h5>{{ $reply->message }}</h5>
                            <br><hr>
                        </div>
                    @endforeach
                    <div>
                        <br>
                        <h5 class="float-right"><span class="badge badge-success">{{ @$client_vendor_ticket->customer->company }}</span></h5>
                        <h5>Posted By <b>{{ @$client_vendor_ticket->user->name }}</b></h5>
                        <h6 class="text-light">{{ date('D j, F, Y', strtotime($client_vendor_ticket->date)) }}</h6>
                        <br><h5>{{ $client_vendor_ticket->message }}</h5><hr>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::open(['route' => 'biller.tenant_tickets.reply', 'method' => 'POST']) }}
                        <h5><b>Reply</b></h5><hr>
                        <div class="form-group row">
                            <div class="col-12">
                                <label for="message" class="caption">Message</label>
                                <div class="input-group">
                                    <div class="w-100">
                                        {{ Form::textarea('message', null, ['class' => 'form-control', 'rows' => 6, 'required' => 'required']) }}
                                        {{ Form::hidden('tenant_ticket_id', $client_vendor_ticket->id) }}
                                    </div>
                                </div>
                            </div>
                        </div> 
                        <div class="edit-form-btn">
                            {{ link_to_route('biller.tenant_tickets.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                            {{ Form::submit('Submit', ['class' => 'btn btn-primary btn-md']) }}
                        </div> 
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    $.ajaxSetup({headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});

    $('#close').click(function(e) {
        e.preventDefault();
        $('#closeForm').submit();
    });
</script>
@endsection