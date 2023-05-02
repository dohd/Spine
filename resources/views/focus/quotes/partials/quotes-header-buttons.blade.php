@php 
    $is_pi = request('page') == 'pi'; 
    $index = 'biller.quotes.index';
    $create = 'biller.quotes.create';
@endphp
<div class="btn-group" role="group" aria-label="quotes">
    <a href="{{ $is_pi ? route($index, 'page=pi') : route($index) }}" class="btn btn-info  btn-lighten-2">
        <i class="fa fa-list-alt"></i> {{trans('general.list')}}
    </a>
    @if ($is_pi)
        <a href="{{ route($create, 'page=pi') }}" class="btn btn-pink  btn-lighten-3 ">
            <i class="fa fa-plus-circle"></i> PI
        </a>
        <a href="{{ route($index) }}" class="btn btn-success ml-1">
            <i class="fa fa-list-alt"></i> Quote
        </a>
    @else
        <a href="{{ route($create) }}" class="btn btn-pink  btn-lighten-3">
            <i class="fa fa-plus-circle"></i> Quote
        </a>
        <a href="{{ route($index, 'page=pi') }}" class="btn btn-success ml-1">
            <i class="fa fa-list-alt"></i> PI
        </a>
    @endif
    &nbsp;
    <a href="{{ route('biller.projects.index') }}" class="btn btn-cyan">
        <i class="fa fa-list-alt"></i> Project
    </a>
</div>