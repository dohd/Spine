@extends ('core.layouts.app')

@section ('title', 'Overtime Rate Management' . ' | ' . 'Create')

@section('page-header')
    <h1>
        {{ 'Overtime Rate Management' }}
        <small>{{ 'Create' }}</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h3 class="content-header-title mb-0">{{ 'View' }}</h3>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.overtimerate.partials.overtimerates-header-buttons')
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
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>OverTime Name</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{$overtimerate['name']}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Rate</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{$overtimerate['rate']}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>{{'Note'}}</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{$overtimerate['note']}}</p>
                                        </div>
                                    </div>


                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
