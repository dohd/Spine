@extends ('core.layouts.app')
@section ('title', trans('import.import'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">{{ trans('features.import') }}</h4>
        </div>
    </div>
    <div class="content-body">
        <div class="card">
            <div class="card-body">
                <div class="card-block">
                    <h4>{{ $data['title'] }}</h4><hr>
                    <p class="alert alert-light mb-3">{{ trans('import.as_per_template') }}. 
                        <a href="{{ route('biller.import.sample_template', $data['type']) }}" target="_blank">
                            <b>{{ trans('import.download_template') }}</b> ({{ $data['title'] }}).
                        </a>
                    </p>
                    <p><strong class="mb-2">File : csv, xls or xlsx</strong></p>
                    {{-- Include template import form --}}
                    @include('focus.import.partials.' . $data['type'])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
