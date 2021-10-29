<div class="">
    <div class="btn-group" role="group" aria-label="quotes">
        <a href="{{ route( 'biller.quotes.index' ) }}" class="btn btn-info  btn-lighten-2 round">
            <i class="fa fa-list-alt"></i> {{trans('general.list')}}
        </a>
        @permission('quote-create')
        <a href="{{ route('biller.quotes.create') }}" class="btn btn-pink  btn-lighten-3 round">
            <i class="fa fa-plus-circle"></i> Quote
        </a>
        <a href="{{ route('biller.quotes.create_pi') }}" class="btn btn-pink  btn-lighten-3 round">
            <i class="fa fa-plus-circle"></i> PI
        </a>
        @endauth
    </div>
</div>
<div class="clearfix"></div>