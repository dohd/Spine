<div class="btn-group" role="group" aria-label="Basic example">
    <a href="{{ route('biller.prospects.index') }}" class="btn btn-info  btn-lighten-2">
        <i class="fa fa-list-alt"></i> {{ trans('general.list') }}
    </a>
    @permission('create-client' ) 
        <a href="{{ route('biller.prospects.create') }}" class="btn btn-pink  btn-lighten-3">
            <i class="fa fa-plus-circle"></i> Prospect
        </a>
        &nbsp;&nbsp;
        <a href="{{ route('biller.leads.create') }}" class="btn btn-success  btn-lighten-3">
            <i class="fa fa-plus-circle"></i> Ticket
        </a>&nbsp;
        
    @endauth
</div>