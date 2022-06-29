@extends('core.layouts.app')

@section('title', 'Direct Purchase | Create')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Direct Purchase Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.purchases.partials.purchases-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="content-body"> 
        <div class="card">
            <div class="card-body">
                {{ Form::open(['route' => 'biller.purchases.store', 'method' => 'POST']) }}
                    @include('focus.purchases.form')
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
@include('focus.purchases.form_js')
@endsection
