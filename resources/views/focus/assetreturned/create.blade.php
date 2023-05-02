@extends('core.layouts.app')

@section('title', 'Asset Return')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="alert alert-warning col-12 d-none budget-alert" role="alert">
            <strong>E.P Margin Not Met!</strong> Check line item rates.
        </div>
    </div>

    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Asset Return</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    <div class="btn-group">
                        <a href="{{ route('biller.assetreturned.create') }}" class="btn btn-primary">
                            <i class="ft-list"></i> List
                        </a>&nbsp;&nbsp;
    
                    </div>                    
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-body">                
                {{ Form::open(['route' => ['biller.assetreturned.store'], 'method' => 'POST']) }}
                    @include('focus.assetissuance.asset-return-form')
                {{ Form::close() }}
            </div>             
        </div>
    </div>
</div>
@endsection