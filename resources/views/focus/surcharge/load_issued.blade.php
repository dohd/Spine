@extends ('core.layouts.app')

@section ('title', 'Individual | Surcharges')

@section('content')
<div>
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">Individual Employee Surcharges</h4>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        {{-- @include('focus.pricelistsSupplier.partials.pricelists-header-buttons') --}}
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
                                <h3>Total Cost of Items Issued to Employee Not Returned</h3>
                                <div class="table-responsive mt-5">
                                    <table class="table text-center tfr my_stripe_single" id="issueTbl">
                                        <thead>
                                            <tr class="item_header bg-gradient-directional-blue white ">
                                                <th class="text-center">Employee Name</th>
                                                <th class="text-center">Requisition Number</th>
                                                <th class="text-center">Total Cost</th>               
                                            </tr>
                                        </thead>
                                        <tbody>
                                           
                                            @foreach ($employees_issued as $employee)
                                               <tr>
                                                <td>{{$employee->employee_name}}</td>
                                               <td><a href="{{route('biller.surcharge.load_items',['requisition'=>$employee->acquisition_number])}}">{{$employee->acquisition_number}}</a></td>
                                               <td>{{$employee->total_cost}}</td>
                                               </tr>
                                           @endforeach
                                        
                                        </tbody>
                                    </table>
                                </div>
                                <hr>
                                <h6>Total: <span class="float-right">{{$cost}}</span></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection