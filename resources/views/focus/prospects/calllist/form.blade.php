<div class="col-sm-12  cmp-pnl">
    <div id="customerpanel" class="inner-cmp-pnl">
        <div class="form-group row">
            <div class="fcol-sm-12">
                <h3 class="title pl-1">Call Allocation</h3>
            </div>
        </div>
        <div class="form-group row">
            <div class='col-sm-6 mb-2'>
                
                    <div><label for="prospect-type">Select Prospect Type</label></div>                        
                    <div class="d-inline-block custom-control custom-checkbox mr-1">
                        <input type="radio" class="custom-control-input bg-primary prospect-type" name="prospect_status" id="colorCheck1" value="direct" checked>
                        <label class="custom-control-label" for="colorCheck1">Direct</label>
                    </div>
                    <div class="d-inline-block custom-control custom-checkbox mr-1">
                        <input type="radio" class="custom-control-input bg-purple prospect-type" name="prospect_status" value="excel" id="colorCheck3">
                        <label class="custom-control-label" for="colorCheck3">Excel Upload</label>
                    </div>
                
            </div>
        </div>
        <div class="form-group row">
            <div class="col-sm-4">
                <p>Number of Prospects : <span id="count"></span></p>
            </div>
            
            
        </div>
        <div class="form-group row">
            <div class="col-sm-4"><label for="group_title" class="caption">Group Title</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                    <select id="group_title" name="group_title" class="form-control" data-placeholder="Choose Title" disabled>
                        @foreach ($excel as $row)
                        <option value="{{ $row->title}}">
                            {{ $row->title }}
                        </option>
                        @endforeach
                        
                    </select>
                </div>
            </div>
            <div class="col-sm-4"><label for="start_date" class="caption">Start date<span class="text-danger">*</span></label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                    {{ Form::text('start_date', null, ['class' => 'form-control datepicker', 'placeholder' => 'Start', 'id' => 'start_date']) }}
                </div>
            </div>
            <div class="col-sm-4"><label for="end_date" class="caption">End date<span class="text-danger">*</span></label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                    {{ Form::text('end_date', null, ['class' => 'form-control datepicker', 'placeholder' => 'End', 'id' => 'end_date']) }}
                </div>
            </div>
        </div>
    </div>
</div>




@section('after-scripts')
    @include('focus.prospects.calllist.form_js')
@endsection
