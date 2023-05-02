<div class="card">
    <div class="card-content">
        <div class="card-body">
            <div class="form-group row mb-2">
                <div class="col-6">
                    <label for="title">Report Subject</label>
                    {{ Form::text('title', null, ['class' => 'form-control', 'required']) }}
                </div>
            </div>
            {{-- tab menu --}}
            <ul class="nav nav-tabs nav-top-border no-hover-bg" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="active-tab1" data-toggle="tab" href="#active1" aria-controls="active1" role="tab" aria-selected="true">Sales</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " id="active-tab2" data-toggle="tab" href="#active2" aria-controls="active2" role="tab">Purchases</a>
                </li>                                     
            </ul>
            <div class="tab-content px-1 pt-1">
                {{-- sales tab --}}
                <div class="tab-pane active in" id="active1" aria-labelledby="active-tab1" role="tabpanel">
                    @include('focus.tax_reports.tabs.sales')
                </div>
                {{-- purchases tab --}}
                <div class="tab-pane" id="active2" aria-labelledby="link-tab2" role="tabpanel">
                    @include('focus.tax_reports.tabs.purchases')
                </div>
                <div class="form-group row no-gutters">
                    <div class="col-1">
                        <a href="{{ route('biller.tax_reports.index') }}" class="btn btn-danger block">Cancel</a>    
                    </div>&nbsp;
                    <div class="col-1">
                        {{ Form::submit(@$tax_report? 'Update' : 'Generate', ['class' => 'form-control btn btn-primary text-white']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('extra-scripts')
@include('focus.tax_reports.form_js')
@endsection
