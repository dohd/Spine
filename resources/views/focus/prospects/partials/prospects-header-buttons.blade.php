<div class="btn-group" role="group" aria-label="Basic example">
    <a href="{{ route('biller.prospects.index') }}" class="btn btn-info  btn-lighten-2">
        <i class="fa fa-list-alt"></i> {{ trans('general.list') }}
    </a>
    @permission('create-client' ) 
        <a href="{{ route('biller.prospects.create') }}" class="btn btn-pink  btn-lighten-3">
            <i class="fa fa-plus-circle"></i> Prospect
        </a>
        &nbsp;&nbsp;
        <a href="{{ route('biller.djcs.create') }}" class="btn btn-success  btn-lighten-3">
            <i class="fa fa-plus-circle"></i> Djc
        </a>&nbsp;
        <a href="{{ route('biller.quotes.create') }}" class="btn btn-warning">
            <i class="fa fa-plus-circle"></i> Quote
        </a>&nbsp;
        <a href="{{ route('biller.quotes.create', 'page=pi') }}" class="btn btn-cyan">
            <i class="fa fa-plus-circle"></i> PI
        </a> 
    @endauth
</div>