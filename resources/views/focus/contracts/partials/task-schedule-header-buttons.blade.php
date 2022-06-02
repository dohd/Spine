<div class="btn-group" role="group" aria-label="Basic example">
    <a href="{{ route('biller.contracts.task_schedule_index') }}" class="btn btn-info  btn-lighten-2">
        <i class="fa fa-list-alt"></i> {{trans( 'general.list' )}}
    </a>
    @permission( 'product-create' )
    <a href="{{ route('biller.contracts.create_schedule_equipment') }}" class="btn btn-pink  btn-lighten-3">
        <i class="fa fa-plus-circle"></i> Load Machine
    </a>
    @endauth
</div>