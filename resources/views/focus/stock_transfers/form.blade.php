<div class="form-group row">
    <div class="col-2">
        <label for="tid">Transfer No.</label>
        {{ Form::text('tid', @$tid+1,['class' => 'form-control round', 'id' => 'tid', 'readonly']) }}
    </div>

    <div class="col-4">
        <label for="warehouse_from">Source Location</label>
        <select name="source_id" id="source_id " class="form-control round" required>
            <option value="">-- select source --</option>
            @foreach ($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}">
                    {{ $warehouse->title }} {{ $warehouse->extra }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-4">
        <label for="warehouse_to">Destination Location</label>
        <select name="destination_id" id="destination_id" class="form-control round" required>
            <option value="">-- select destination --</option>
            @foreach ($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}">
                    {{ $warehouse->title }} {{ $warehouse->extra }}
                </option>
            @endforeach
        </select>
    </div>  
</div>

<div class="row">
    <div class="col-10">
        <label for="note">Note</label>
        {{ Form::text('note', null, ['class' => 'form-control round', 'id' => 'viable_days', 'readonly']) }}
    </div>
</div>
<br>
<div class="table-responsive">
    <table class="table tfr my_stripe_single text-center" id="productsTbl">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th width="5%">#</th>
                <th width="25%">Item Description</th>
                <th width="10%">Qty</th>
                <th>UoM</th>
                <th>Unit Cost</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
        </tbody>  
    </table>
</div>

<div class="form-group row no-gutters">
    <div class="col-1 ml-auto">
        <a href="{{ route('biller.leave.index') }}" class="btn btn-danger block">Cancel</a>    
    </div>
    <div class="col-1 ml-1">
        {{ Form::submit(@$leave? 'Update' : 'Create', ['class' => 'form-control btn btn-primary text-white']) }}
    </div>
</div>

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    const Index = {
        stockTransfer: @json(@$stock_transfer),

        init() {
            $.ajaxSetup(config.ajax);
            
        },

        
    };

    $(() => Index.init());
</script>
@endsection