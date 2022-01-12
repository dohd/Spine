<div>
    <div class="btn-group" role="group" aria-label="quotes">
        @php
            $link = route('biller.quotes.index');
            $prev_url = url()->previous();
            if (strpos($prev_url, 'page=pi')) $link = route('biller.quotes.index', 'page=pi');
            $curr_uri = $_SERVER['REQUEST_URI'];
            if (strpos($curr_uri, 'page=pi')) $link = route('biller.quotes.index', 'page=pi');
        @endphp
        <a href="{{ $link }}" class="btn btn-info  btn-lighten-2">
            <i class="fa fa-list-alt"></i> {{trans('general.list')}}
        </a>

        @permission('quote-create')
            @if (request()->getQueryString() == 'page=pi')
                <a href="{{ route('biller.quotes.create', 'page=pi') }}" class="btn btn-pink  btn-lighten-3">
                    <i class="fa fa-plus-circle"></i> PI
                </a>&nbsp;&nbsp;
                <a href="{{ route('biller.quotes.index') }}" class="btn btn-success">
                    <i class="fa fa-list-alt"></i> Quote
                </a>&nbsp;&nbsp;
            @else
                <a href="{{ route('biller.quotes.create') }}" class="btn btn-pink  btn-lighten-3">
                    <i class="fa fa-plus-circle"></i> Quote
                </a>&nbsp;&nbsp;
                <a href="{{ route('biller.quotes.index', 'page=pi') }}" class="btn btn-success">
                    <i class="fa fa-list-alt"></i> PI
                </a>&nbsp;&nbsp;
            @endif
        @endauth

        <a href="{{ route('biller.projects.index') }}" class="btn btn-cyan">
            <i class="fa fa-list-alt"></i> Project
        </a>
    </div>
</div>
