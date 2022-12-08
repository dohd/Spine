@extends ('core.layouts.app')

@section ('title', 'View | Equipment Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Equipment Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.equipments.partials.equipments-header-buttons')
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
                            <table class="table table-bordered table-sm">
                                @php
                                    $details = [ 
                                        'Equipment No' => gen4tid('Eq-', $equipment->tid),
                                        'Customer' => $equipment->customer? $equipment->customer->company : '',    
                                        'Branch' => $equipment->branch? $equipment->branch->name : '',
                                        'Equipment Category' => $equipment->category? $equipment->category->name : '',
                                        'Client Tag No' => $equipment->unique_id,
                                        'Serial No' => $equipment->equip_serial,
                                        'Make / Type' => $equipment->make_type,
                                        'Model / Model Number' => $equipment->model,
                                        'Capacity' => $equipment->capacity,
                                        'Gas / Fuel Type' => $equipment->machine_gas,
                                        'Equipment Location' => $equipment->location,
                                        'Equipment Building' => $equipment->building,
                                        'Building Floor' => $equipment->floor,
                                        'Service Rate (VAT Exc)' => numberFormat($equipment->service_rate),
                                        'Installation Date' => $equipment->install_date? dateFormat($equipment->install_date) : '',
                                        'Remark' => $equipment->note,
                                    ];
                                @endphp
                                @foreach ($details as $key => $val)
                                <tr>
                                    <th width="50%">{{ $key }}</th>
                                    <td>{{ $val }}</td>
                                </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection