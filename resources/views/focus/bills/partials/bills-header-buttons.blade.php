<div class="btn-group">
    <a href="{{ route('biller.bills.create_kra') }}" class="btn btn-secondary btn-lighten-2 ml-1">
        <i class="fa fa-plus-circle"></i> KRA
    </a>
    <a href="{{ route('biller.bills.index') }}" class="btn btn-info btn-lighten-2">
        <i class="fa fa-list-alt"></i> {{ trans('general.list') }}
    </a>
    <a href="{{ route('biller.bills.create') }}" class="btn btn-purple  btn-lighten-3 ml-1">
        <i class="fa fa-money"></i> Pay
    </a>
</div>