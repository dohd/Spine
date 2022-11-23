<div class="btn-group">
    <a href="{{ route('biller.quotes.get_verify_quote') }}" class="btn btn-success">
        <i class="fa fa-list-alt"></i> Verification
    </a> &nbsp;&nbsp;
    <a href="{{ route('biller.projects.index') }}" class="btn btn-info  btn-lighten-2">
        <i class="fa fa-list-alt"></i> List
    </a>
    @if (!strpos(request()->url(), 'edit'))
        <a href="#" class="btn btn-pink btn-lighten-3" id="addt" data-toggle="modal" data-target="#AddProjectModal">
            <i class="fa fa-plus-circle"></i> Create
        </a>
    @endif
</div>