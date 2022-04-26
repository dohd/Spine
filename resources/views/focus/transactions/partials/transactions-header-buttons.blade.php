<div class="btn-group" role="group" aria-label="Basic example">
    @permission( 'transaction-data' )
        <a href="{{ route( 'biller.journals.create' ) }}" class="btn btn-pink  btn-lighten-3">
            <i class="fa fa-plus-circle"></i> {{trans( 'general.create' )}}
        </a>
    @endauth
</div>