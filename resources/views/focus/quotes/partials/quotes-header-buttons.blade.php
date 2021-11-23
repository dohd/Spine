<div>
    <div class="btn-group" role="group" aria-label="quotes">
        @php
            $is_pi = request()->getQueryString();
            $url = $is_pi ? route('biller.quotes.index', $is_pi) : route('biller.quotes.index');
        @endphp
        <a href="{{ $url }}" class="btn btn-info  btn-lighten-2 round">
            <i class="fa fa-list-alt"></i> {{trans('general.list')}}
        </a>
        @permission('quote-create')
        <a href="{{ route('biller.quotes.create') }}" class="btn btn-pink  btn-lighten-3 round">
            <i class="fa fa-plus-circle"></i> Quote
        </a>
        <a href="{{ route('biller.quotes.create', 'page=pi') }}" class="btn btn-pink  btn-lighten-3 round">
            <i class="fa fa-plus-circle"></i> PI
        </a>
        @endauth
    </div>
</div>
