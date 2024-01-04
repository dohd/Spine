<div class="row mb-1">
    <div class="col-6">
        <h6 class="mb-2">Package Info</h6>
        <div class="row">
            <div class="col-12">
                <div class='form-group'>
                    {{ Form::label('package_name', 'Package Name', ['class' => 'col control-label']) }}
                    <div class='col'>
                        {{ Form::text('name', null, ['class' => 'form-control box-size', 'placeholder' => 'Package Name', 'id' => 'name', 'required' => 'required']) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label('cost', 'Package Cost', ['class' => 'col control-label']) }}
                    <div class='col'>
                        {{ Form::text('cost', null, ['class' => 'form-control box-size', 'placeholder' => 'Package Cost', 'id' => 'cost', 'required' => 'required']) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label('maintenance_cost', 'Maintenance Cost', ['class' => 'col control-label']) }}
                    <div class='col'>
                        {{ Form::text('maintenance_cost', null, ['class' => 'form-control box-size', 'placeholder' => 'Maintenance Cost', 'id' => 'maintenance_cost', 'required' => 'required']) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label('maintenance_term', 'Maintenance Term (Months)', ['class' => 'col control-label']) }}
                    <div class='col'>
                        {{ Form::text('maintenance_term', 12, ['class' => 'form-control box-size', 'placeholder' => 'Maintenance Term', 'id' => 'maintenance_term', 'required' => 'required']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6">
        <h6 class="mb-2 ml-1">Package Extras</h6>
        <div class='form-group mb-3'>
            {{ Form::label('extras_term', 'Package Extras Term (Months)', ['class' => 'col control-label']) }}
            <div class='col'>
                {{ Form::text('extras_term', 12, ['class' => 'form-control box-size', 'placeholder' => 'Package Extras Term', 'id' => 'extras_term']) }}
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-flush-spacing">
                <tbody>
                    @foreach ($package_extras as $package)
                        <tr>
                            <td class="text-nowrap fw-bolder">{{ $package->name }}</td>
                            <td><input type="text" class="form-control col-10 extra-cost" placeholder="Cost" name="extra_cost[]" value="{{ $package->extra_cost }}"></td>
                            <td><input type="checkbox" class="form-check-input select" name="package_id[]" value="{{ $package->id }}" {{ $package->checked }}></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>        
        </div>
    </div>
</div> 
<div class="row mb-2">
    <div class="col-12">
        <h5 class="ml-2 font-weight-bold">Total Cost: <span class="total-cost"></span></h5>
        {{ Form::hidden('total_cost', null, ['id' => 'total-cost']) }}
        {{ Form::hidden('extras_total', null, ['id' => 'extras-cost']) }}
    </div>
</div>

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    $.ajaxSetup(config.ajax);
    $('form').on('keyup', '#cost, #maintenance_cost', function() {
        calcTotals();
    });
    $('table').on('change', '.select', function() {
        calcTotals();
    });

    function calcTotals() {
        const pkgCost = accounting.unformat($('#cost').val()); 
        const maintCost = accounting.unformat($('#maintenance_cost').val()); 
        let extraCost = 0;
        $('table .select').each(function() {
            const row = $(this).parents('tr');
            if ($(this).prop('checked')) {
                extraCost += accounting.unformat(row.find('.extra-cost').val()); 
            }
        });
        const total = pkgCost+maintCost+extraCost;
        $('.total-cost').text(accounting.formatNumber(total));
        $('#total-cost').val(accounting.formatNumber(total));
        $('#extras-cost').val(accounting.formatNumber(extraCost));
    }
    
    $('form').submit(function(e) {
        $('table .select').each(function() {
            const row = $(this).parents('tr');
            if (!$(this).prop('checked')) row.remove();
        });
    });

    const service = @json(@$tenant_service);
    if (service && service.id) {
        $('#cost').keyup();
    }
</script>
@endsection