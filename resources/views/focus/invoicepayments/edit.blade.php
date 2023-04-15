@extends ('core.layouts.app')

@section('title', 'Edit | Invoice Payment Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Invoice Payment Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.invoicepayments.partials.invoicepayment-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::model($invoice_payment, ['route' => array('biller.invoicepayments.update', $invoice_payment), 'method' => 'PATCH']) }}
                        @include('focus.invoicepayments.form')
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
@include('focus/invoicepayments/form_js')
@endsection

