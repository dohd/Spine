@extends ('core.layouts.app')

@section('title', 'Edit | Refill Product Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Edit Refill Product </h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.refill_products.partials.refill-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::model($product_refill, ['route' => ['biller.refill_products.update', $product_refill], 'method' => 'PATCH']) }}
                        @include('focus.refill_products.form')
                    {{ Form::close() }}
                </div>
            </div
        </div>
    </div>
</div>
@endsection
