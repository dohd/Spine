@extends('core.layouts.app')

@php
    $header_title = trans('labels.backend.quotes.management');
    $is_pi = request('page') == 'pi';
    $task = request('task');
    if ($is_pi) $header_title = 'Proforma Invoice Management';
@endphp

@section('title', 'Create | ' . $header_title)

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">{{ $header_title }}</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                @include('focus.quotes.partials.quotes-header-buttons')
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
        {{ Form::open(['route' => 'biller.quotes.store', 'method' => 'POST', 'id' => 'quoteForm']) }}
            @include('focus.quotes.form')
            <div class="edit-form-btn row">
                {{ link_to_route('biller.quotes.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md col-1 ml-auto mr-1']) }}
                {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-md col-1 mr-2']) }}                                           
            </div>
        {{ Form::close() }}
        </div>
    </div> 
</div>
@endsection

@section('extra-scripts')
@include('focus.quotes.create_js')
@endsection