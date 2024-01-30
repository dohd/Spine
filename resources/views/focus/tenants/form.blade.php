<div class="row">
    <div class="col-6">
        <div class="card rounded pb-5">
            <div class="card-content pb-5">
                <div class="card-body">
                    <h6 class="mb-2">Business Info</h6>
                    <div class='form-group'>
                        {{ Form::label('customer', 'Search Business', ['class' => 'col control-label']) }}
                        <div class='col'>
                            <select name="customer_id" id="customer"  data-placeholder="Search Business">
                                @if (isset($tenant->package->customer))
                                    <option selected value="{{ @$tenant->package->customer_id }}">{{ $tenant->package->customer->company }}</option>
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class='form-group'>
                            {{ Form::label('cname', 'Business Name', ['class' => 'col control-label']) }}
                            <div class='col'>
                                {{ Form::text('cname', @$tenant['cname'], ['class' => 'form-control box-size', 'placeholder' => 'Business Name', 'cname' => 'cname', 'required' => 'required', 'readonly' => 'readonly']) }}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class='form-group'>
                                    {{ Form::label('address', 'Street Address', ['class' => 'col control-label']) }}
                                    <div class='col-12'>
                                        {{ Form::text('address', @$tenant['address'], ['class' => 'form-control box-size', 'placeholder' => 'Street Address', 'required' => 'required']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class='form-group'>
                                    {{ Form::label('country', trans('hrms.country'), ['class' => 'col control-label']) }}
                                    <div class='col-12'>
                                        {{ Form::text('country', @$tenant['country'], ['class' => 'form-control box-size', 'placeholder' => trans('hrms.country'), 'country' => 'country', 'required' => 'required', 'readonly' => 'readonly']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class='form-group'>
                            {{ Form::label('postbox', trans('hrms.postal'), ['class' => 'col control-label']) }}
                            <div class='col'>
                                {{ Form::text('postbox', @$tenant['postbox'], ['class' => 'form-control box-size', 'placeholder' => trans('hrms.postal'), 'required' => 'required']) }}
                            </div>
                        </div>                        
                        <div class='form-group'>
                            {{ Form::label('email', 'Email Address', ['class' => 'col control-label']) }}
                            <div class='col'>
                                {{ Form::text('email', @$tenant['email'], ['class' => 'form-control box-size', 'placeholder' => 'Email Address', 'email' => 'email', 'required' => 'required', 'readonly' => 'readonly']) }}
                            </div>
                        </div>
                        <div class='form-group'>
                            {{ Form::label('phone', trans('general.phone'), ['class' => 'col control-label']) }}
                            <div class='col'>
                                {{ Form::text('phone', @$tenant['phone'], ['class' => 'form-control box-size', 'placeholder' => trans('general.phone'), 'phone' => 'phone', 'required' => 'required', 'readonly' => 'readonly']) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Package Info --}}
    <div class="col-6 pl-0">
        <div class="card rounded">
            <div class="card-content">
                <div class="card-body">
                    <div class="row mb-1">
                        <div class="col-12">
                            <h6 class="mb-2">Package Info</h6>
                            <div class="row">
                                <div class="col-12">
                                    <div class='form-group'>
                                        {{ Form::label('date', 'Date', ['class' => 'col control-label']) }}
                                        <div class='col'>
                                            {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required' => 'required']) }}
                                        </div>
                                    </div>
                                    <div class='form-group'>
                                        {{ Form::label('account_plan', 'Account Plan', ['class' => 'col control-label']) }}
                                        <div class='col'>
                                            <select name="package_id" id="package" class="custom-select">
                                                @foreach ($tenant_services as $i => $service)
                                                    <option 
                                                        value="{{ $service['id'] }}" 
                                                        category="{{ $service['name'] }}"
                                                        cost="{{ $service['cost'] }}" 
                                                        maint_cost="{{ $service['maintenance_cost'] }}"
                                                        {{ $service['id'] == @$tenant->package->package_id? 'selected' : '' }}
                                                    >
                                                        {{ $service['name'] }} Package - {{ amountFormat($service['cost']) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class='form-group'>
                                        {{ Form::label('subscr_term', 'Subscription Term', ['class' => 'col control-label']) }}
                                        <div class='col'>
                                            @php
                                                $terms = ['12' => 'ANNUALLY', '6' => '6 MONTHS', '3' => '3 MONTHS'];
                                            @endphp
                                            <select name="subscr_term" id="subscr_term" class="custom-select">
                                                @foreach ($terms as $key => $value)
                                                    <option value="{{ $key }}" {{ @$tenant->package->subscr_term == $key ? 'selected' : '' }}>
                                                        {{ $value }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class='form-group'>
                                        {{ Form::label('maintenance_cost', 'Maintenance Cost', ['class' => 'col control-label']) }}
                                        <div class='col'>
                                            {{ Form::text('maintenance_cost', null, ['class' => 'form-control box-size', 'placeholder' => 'Maintenance Cost', 'id' => 'maintenance_cost', 'readonly' => 'readonly']) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <h6 class="mb-4 ml-1">Package Extras</h6>
                            <div class="table-responsive">
                                <table class="table table-flush-spacing">
                                    <thead>
                                        <tr>
                                            <th>Module</th>
                                            <th>Pkg Cost</th>
                                            <th>Mtn Cost.</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($tenant_services as $service)
                                            @foreach ($service->items as $item)
                                                <tr class="grp-{{ $service['name'] }} d-none">
                                                    <td class="text-nowrap fw-bolder"><span class="me-1">{{ $item->package_extra->name }} ({{ $service['name'] }})</span> </td>
                                                    <td><label class="form-check-label maint-cost" for="maint-cost"> {{ numberFormat($item['maint_cost']) }} </label></td>
                                                    <td><label class="form-check-label" for="extra-cost"> {{ numberFormat($item['extra_cost']) }} </label></td>
                                                    <td>
                                                        <input class="form-check-input ml-1 select" type="checkbox" value="{{ $item['extra_cost'] }}"/>
                                                        {{ Form::hidden('package_item_id[]', $item['id'], ['class' => 'item_id', 'disabled' => 'disabled']) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>        
                            </div>
                        </div>
                    </div> 
                    <div class="row">
                        <div class="col-12">
                            <h5 class="ml-2 font-weight-bold">Total Cost: <span class="total-cost"></span></h5>
                            {{ Form::hidden('cost', null, ['id' => 'cost']) }}
                            {{ Form::hidden('total_cost', null, ['id' => 'total_cost']) }}
                            {{ Form::hidden('extras_cost', null, ['id' => 'extras_cost']) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section("after-scripts")
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    const config = {
        ajax: { headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } },
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
        customerSelect2: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.tenants.customers') }}",
                dataType: 'json',
                delay: 250,
                method: 'POST',
                data: ({term}) => ({q: term}),
                processResults: data => {
                    return {
                        results: data.map(v => ({
                            id: v.id,
                            text: v.company, 
                            company: v.company,
                            country: v.country,
                            email: v.email,
                            phone: v.phone,
                        }))
                    }
                },
            },
        },
    };

    $.ajaxSetup(config.ajax);
    $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
    $('#customer').select2(config.customerSelect2);
    $('#customer').change(function() {
        const data = $(this).select2('data')[0];
        if (data && data.id) {
            $('#cname').val(data.company);
            $('#country').val(data.country);
            $('#email').val(data.email);
            $('#phone').val(data.phone);
        } 
        else ['cname', 'country', 'email', 'phone'].forEach(v => $('#'+v).val(''));
    });

    $('#package').change(function() {
        const option = $(this).find(':selected');
        const maint_cost = accounting.unformat(option.attr('maint_cost'));
        let line_maint_cost = 0;
        $('table tbody tr').each(function() {
            const name = 'grp-'+option.attr('category');
            if ($(this).hasClass(name)) {
                $(this).removeClass('d-none')
            } else if (!$(this).hasClass('d-none')) {
                $(this).addClass('d-none');
            }
            if ($(this).find('input').is(':checked')) {
                line_maint_cost += accounting.unformat($(this).find('.maint-cost').text());
            }
        });

        $('#maintenance_cost').val(accounting.formatNumber(maint_cost+line_maint_cost));
        totalCost();
    }).change();

    $('table').on('change', '.select', function() {
        if ($(this).is(':checked')) {
            $(this).next().attr('disabled', false);
        } else {
            $(this).next().attr('disabled', true);
        }
        totalCost();
    });

    function totalCost() {
        const option = $('#package').find(':selected');
        const cost = accounting.unformat(option.attr('cost'));
        const maint_cost = accounting.unformat(option.attr('maint_cost'));
        let extra_cost = 0;
        let line_maint_cost = 0;
        $('table input').each(function() {
            const row = $(this).parents('tr');
            if (!row.hasClass('d-none')) {
                if ($(this).is(':checked')) {
                    extra_cost += accounting.unformat($(this).val());
                    line_maint_cost +=  accounting.unformat(row.find('.maint-cost').text());
                }
            }
        });
        const total = cost + maint_cost + extra_cost + line_maint_cost;
        $('#maintenance_cost').val(accounting.formatNumber(maint_cost+line_maint_cost));
        $('.total-cost').text(accounting.formatNumber(total));
        $('#total_cost').val(total);
        $('#cost').val(cost);
        $('#extras_cost').val(extra_cost);
    }

    const tenant = @json(@$tenant);
    /** Edit Mode */
    if (tenant) {
        const date = tenant?.package.date
        if (date) $('.datepicker').datepicker('setDate', new Date(date));

        let package_items = @json(@$tenant->package->items);
        if (package_items) {
            package_items = package_items.map(v => v.package_item_id);
            $('.item_id').each(function() {
                if (!$(this).parents('tr').hasClass('d-none')) {
                    const val = this.value * 1;
                    if (package_items.includes(val)) {
                        $(this).prev().prop('checked', true).change();
                    }
                }
            });
        }
    } 
</script>
@endsection    