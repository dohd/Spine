@extends ('core.layouts.app')

@section('title', 'Tenant Tickets Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Tenant Tickets Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.tenant_tickets.partials.tenant-tickets-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <button type="button" class="btn btn-danger float-right"><i class="fa fa-times" aria-hidden="true"></i> Closed</button>
                    <button type="button" class="btn btn-outline-secondary float-right mr-1"><i class="fa fa-pencil" aria-hidden="true"></i> Reply</button>                    
                    <h3 class="text-success mb-1">{{ gen4tid('#TKT-', $tenant_ticket->tid) }}</h3>
                    <h5>Subject: <b>{{ $tenant_ticket->subject }}</b></h5>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <div>
                        <h5 class="float-right"><span class="badge badge-info">Operator</span></h5>
                        <h5>Posted By <b>John Doe</b></h5>
                        <h6 class="text-light">{{ date('D j, F, Y', strtotime($tenant_ticket->date)) }}</h6>
                        <br>
                        <h5>
                            Greetings,<br>Thank you for your ticket.Kindly note that the password has been reset and shared via your email.
                            <br>For any other issue or for further inquiries, do not hesitate to contact us
                        </h5>
                        <hr>
                    </div>
                    <div>
                        <br>
                        <h5 class="float-right"><span class="badge badge-success">Owner</span></h5>
                        <h5>Posted By <b>{{ auth()->user()->name }}</b></h5>
                        <h6 class="text-light">{{ date('D j, F, Y', strtotime($tenant_ticket->date)) }}</h6>
                        <br><h5>{{ $tenant_ticket->message }}</h5><hr>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <h5><b>Reply</b></h5><hr>
                    <div class="form-group row">
                        <div class="col-12">
                            <label for="message" class="caption">Message</label>
                            <div class="input-group">
                                <div class="w-100">
                                    {{ Form::textarea('message', null, ['class' => 'form-control', 'rows' => 6, 'required' => 'required']) }}
                                </div>
                            </div>
                        </div>
                    </div> 
                    <div class="edit-form-btn">
                        {{ link_to_route('biller.tenant_tickets.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                        {{ Form::submit('Submit', ['class' => 'btn btn-primary btn-md']) }}
                    </div> 
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
