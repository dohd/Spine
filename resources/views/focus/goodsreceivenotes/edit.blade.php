@extends ('core.layouts.app')

@section('title', 'Goods Receive Note')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Goods Receive Note</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.goodsreceivenotes.partials.goodsreceivenotes-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::model($goodsreceivenote, ['route' => array('biller.goodsreceivenote.update', $goodsreceivenote), 'method' => 'PATCH']) }}
                        @include('focus.goodsreceivenotes.form')
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
