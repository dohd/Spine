<div>
    <div class="btn-group">
        @permission('project-create')
        <a href="#" class="btn btn-info w-2" id="addt" data-toggle="modal" data-target="#AddProjectModal">
            <i class="fa fa-plus-circle"></i> Create
        </a>&nbsp;&nbsp;
        @endauth
        <a href="{{ route('biller.quotes.project_quotes') }}" class="btn btn-success">
            <i class="fa fa-list-alt"></i> Verification
        </a>  
    </div>
</div>
