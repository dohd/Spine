<div class="btn-group" role="group" aria-label="invoice-buttons">
    <a href="{{ route('biller.invoices.index' ) }}" class="btn btn-info  btn-lighten-2">
        <i class="fa fa-list-alt"></i> {{trans('general.list')}}
    </a>
    @permission('create-invoice')
        <a href="{{ route('biller.invoices.uninvoiced_quote') }}" class="btn btn-pink btn-lighten-3">
            <i class="fa fa-plus-circle"></i> {{trans('general.create')}}
        </a>
    @endauth
</div>