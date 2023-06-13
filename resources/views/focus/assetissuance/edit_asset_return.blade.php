@extends('core.layouts.app')

@section('title', 'Edit | Project Budget')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="alert alert-warning col-12 d-none budget-alert" role="alert">
            <strong>E.P Margin Not Met!</strong> Check line item rates.
        </div>
    </div>

    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Project Budget Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    <div class="btn-group">
                        <a href="{{ route('biller.projects.index') }}" class="btn btn-primary">
                            <i class="ft-list"></i> Projects
                        </a>&nbsp;&nbsp;
    
                    </div>                    
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-body">                
                {{ Form::model($assetissuance, ['route' => ['biller.assetissuance.update_asset',$assetissuance], 'method' => 'PATCH']) }}
                    @include('focus.assetissuance.asset-return-form')
                {{ Form::close() }}
            </div>             
        </div>
    </div>
</div>
@endsection