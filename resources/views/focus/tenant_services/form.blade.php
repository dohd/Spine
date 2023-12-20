<div class="form-group row">
    <div class="col-md-5 col-12">
        <label for="company">Company</label>
        <select name="company_id" id="company" class="form-control" data-placeholder="Search Company" required>
            @if(isset($tenant_service))
                <option value="{{ $tenant_service->company_id }}" selected>
                    {{ @$tenant_service->company->cname }}
                </option>
            @endif
        </select>
    </div>

    <div class="col-md-2">
        <label for="cost">Service Cost</label>
        {{ Form::text('cost', null, ['class' => 'form-control', 'id' => 'cost', 'required']) }}
    </div>

    <div class="col-md-3">
        <label for="subscription">Subscription</label>
        <select name="subscription" id="subscription" class="custom-select">
            @foreach (['Monthly', 'Quaterly', 'Yearly'] as $i => $value)
                <option value="{{ $value }}" {{ @$tenant_service->subscription == $value? 'selected' : '' }}>
                    {{ $value }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-2">
        <label for="date" class="caption">Date</label>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date']) }}
    </div>
</div>
<div class="form-group row">
    <div class="col-md-5">
        <label for="category">Service Category</label>
        <select name="category" id="category" class="custom-select">
            @foreach (['Maintenance'] as $i => $value)
                <option value="{{ $value }}">
                    {{ $value }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-7">
        <label for="cost">Service Description</label>
        {{ Form::text('description', null, ['class' => 'form-control', 'id' => 'description', 'required']) }}
    </div>
</div>

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
        companySelect2: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.tenants.select') }}",
                dataType: 'json',
                delay: 250,
                method: 'POST',
                data: ({term}) => ({q: term}),
                processResults: data => {
                    return {results: data.map(v => ({text: v.cname, id: v.id}))}
                }
            },
        }
    };

    const Index = {
        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#company').select2(config.companySelect2);

            const service = @json(@$tenant_service);
            if (service.id) {
                $('#date').datepicker('setDate', new Date(service.date));
            }
        },
    };

    $(Index.init);
</script>
@endsection