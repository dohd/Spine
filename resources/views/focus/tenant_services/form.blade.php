<div class="card rounded">
    <div class="card-content">
        <div class="card-body">
            <div class="row mb-1">
                <div class="col-12">
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
                        </div>
                    </div>
                </div>
            </div> 
        </div>
    </div>
</div>  

{{-- select modules --}}
<div class="card rounded">
    <div class="card-content">
        <div class="card-body">
            <h5 class="ml-1">Active Modules</h5>
            <div class="table-responsive">
                <table class="table table-flush-spacing" id="modulesTbl">
                    <tbody>
                        <tr>
                            <td class="text-nowrap fw-bolder">
                                <div class="row">
                                    @foreach ($package_extras as $i => $package)
                                        <div class="col-3 mb-1">
                                            <div class="row">
                                                <div class="col-8">{{ $package->name }}</div>
                                                <div class="col-4">
                                                    <input type="checkbox" class="form-check-input select" name="module_id[]" value="{{ $package->id }}" id="mod-{{ $package->id }}">
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </td>    
                        </tr>
                    </tbody>
                </table>        
            </div>
        </div>
    </div>
</div>  

{{-- package extras --}}
<div class="card rounded">
    <div class="card-content">
        <div class="card-body">
            @if (count($package_extras))
                <div class="row mb-2">
                    <div class="col-12">
                        <h6 class="mb-2 ml-1">Package Extras</h6>
                        <div class="row">
                            <div class="col-md-10 ml-auto mr-auto">
                                <div class="table-responsive">
                                    <table class="table table-flush-spacing" id="extrasTbl">
                                        <tbody>
                                            @foreach ($package_extras as $package)
                                                <tr>
                                                    <td class="text-nowrap fw-bolder">{{ $package->name }}</td>
                                                    <td><input type="checkbox" class="form-check-input select" name="package_id[]" value="{{ $package->id }}" {{ $package->checked }}></td>
                                                    <td><input type="text" class="form-control col-10 pb-0 pt-0 extra-cost" placeholder="Package Cost" name="extra_cost[]" value="{{ $package->extra_cost }}"></td>
                                                    <td><input type="text" class="form-control col-10 pb-0 pt-0 maint-cost" placeholder="Maintenance Cost" name="maint_cost[]" value="{{ $package->maint_cost }}"></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>        
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="row">
                <div class="col-12">
                    <h5 class="ml-2 font-weight-bold">Total Cost: <span class="total-cost"></span></h5>
                    {{ Form::hidden('total_cost', null, ['id' => 'total-cost']) }}
                    {{ Form::hidden('extras_total', null, ['id' => 'extras-cost']) }}
                </div>
            </div>
        </div>
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
    $('#extrasTbl').on('change', '.select', function() {
        calcTotals();
    });

    function calcTotals() {
        const pkgCost = accounting.unformat($('#cost').val()); 
        const maintCost = accounting.unformat($('#maintenance_cost').val()); 
        let extraCost = 0;
        let lineMaintCost = 0;
        $('#extrasTbl .select').each(function() {
            const row = $(this).parents('tr');
            if ($(this).prop('checked')) {
                extraCost += accounting.unformat(row.find('.extra-cost').val()); 
                extraCost += accounting.unformat(row.find('.maint-cost').val()); 
            }
        });
        const total = pkgCost + maintCost + extraCost + lineMaintCost;
        $('.total-cost').text(accounting.formatNumber(total));
        $('#total-cost').val(accounting.formatNumber(total));
        $('#extras-cost').val(accounting.formatNumber(extraCost));
    }
    
    $('form').submit(function(e) {
        $('#extrasTbl .select').each(function() {
            const row = $(this).parents('tr');
            if (!$(this).prop('checked')) row.remove();
        });
    });

    const service = @json(@$tenant_service);
    if (service && service.id) {
        $('#cost').keyup();
        const module_ids = service.module_id? service.module_id.split(',') : [];
        $('#modulesTbl .select').each(function() {
            const id = $(this).attr('id').split('-')[1];
            if (module_ids.includes(id+'')) $(this).prop('checked', true);
        });
    }
</script>
@endsection