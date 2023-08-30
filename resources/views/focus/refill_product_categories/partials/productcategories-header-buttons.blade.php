<div class="btn-group" role="group" aria-label="Basic Example">
    <a href="{{ route('biller.refill_product_categories.index') }}" class="btn btn-info  btn-lighten-2">
        <i class="fa fa-list-alt"></i> {{trans( 'general.list' )}}
    </a>
    @permission('create-refill-product-category')
    <a href="{{ route('biller.refill_product_categories.create') }}" class="btn btn-pink  btn-lighten-3">
        <i class="fa fa-plus-circle"></i> {{trans( 'general.create' )}}
    </a>
    @endauth
</div>
