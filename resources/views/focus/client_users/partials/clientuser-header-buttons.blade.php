<div class="btn-group" role="group" aria-label="Basic example">
    <a href="{{ route('biller.client_users.index') }}" class="btn btn-info  btn-lighten-2">
        <i class="fa fa-list-alt"></i> {{ trans('general.list') }}
    </a>            
    <a href="{{ route('biller.client_users.create') }}" class="btn btn-pink  btn-lighten-3">
        <i class="fa fa-plus-circle"></i> {{ trans('general.create') }}
    </a>
</div>