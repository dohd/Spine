@extends ('core.layouts.app')

@section ('title', trans('labels.backend.accounts.management') . ' | ' . trans('labels.backend.accounts.create'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h3 class="mb-0">{{ trans('labels.backend.accounts.view') }}</h3>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.accounts.partials.accounts-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            @php
                                $account_details = [
                                    trans('accounts.number') => $account['number'],
                                    trans('accounts.holder') => $account['holder'],
                                    trans('accounts.balance') => amountFormat($account['balance']),
                                    trans('accounts.code') => $account['code'],
                                    trans('accounts.account_type') => $account['account_type'],
                                    trans('accounts.note') => $account['note'],
                                ];
                            @endphp
                            @foreach ($account_details as $key => $value)
                                <div class="row">
                                    <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                        <p>{{ $key }} </p>
                                    </div>
                                    <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                        <p> 
                                            {{ $value }} &nbsp;&nbsp;
                                            @if ($key == trans('accounts.number'))
                                                <a class="btn btn-purple round" href="{{ route('biller.transactions.index') }}?rel_type=9&rel_id={{ $account['id'] }}" title="List">
                                                    <i class="fa fa-list"></i>
                                                </a>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            @endforeach                         
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
