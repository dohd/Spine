@extends ('core.layouts.app')

@section ('title', 'Salary Management' . ' | ' . 'Create')

@section('page-header')
    <h1>
        {{ 'Salary Management' }}
        <small>{{ 'Create' }}</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h3 class="content-header-title mb-0">{{ 'View Salary' }}</h3>
                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.salary.partials.salary-header-buttons')
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card-header">
                            <button class="btn btn-primary" id="renew_contract" data-toggle="modal" data-target="#renew">Renew</button>
                            <button class="btn btn-danger ml-5" id="terminate_contract" data-toggle="modal" data-target="#terminate">Terminate</button>
                        </div>
                        <div class="card">

                            <div class="card-content">

                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Employee Name</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{$salary['employee_name']}}</p>
                                            <input type="hidden" id="salary_employee" data-name="{{$salary['employee_name']}}"  value="{{$salary['employee_name']}}">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Basic Pay</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{amountFormat($salary['basic_pay'])}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Contract Type</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{$salary['contract_type']}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>House Allowance</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{amountFormat($salary['house_allowance'])}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Transport Allowance</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{amountFormat($salary['transport_allowance'])}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Directors Fee</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{amountFormat($salary['directors_fee'])}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Start Date</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{$salary['start_date']}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Duration (In Months)</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{$salary['duration']}}</p>
                                        </div>
                                    </div>


                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('focus.salary.partials.terminate-contract')
    </div>
    @include('focus.salary.partials.add-renew')
@endsection

@section('extra-scripts')
    <script>
        $('#renew_contract').click(function (e) { 
            var name = $('#salary_employee').val();
            $('#employee').val(name);
        });
    </script>
@endsection
