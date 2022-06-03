@extends ('core.layouts.app')

@section ('title', 'View | Contract Management')

@section('content')
<div>
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">Contract Management</h4>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        @include('focus.contracts.partials.contracts-header-buttons')
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
                                @php
                                    $details = [
                                        'Contract No' => $contract->tid,
                                        'Title' => $contract->title,
                                        'Amount' => numberFormat($contract->amount),
                                        'Start Date' => dateFormat($contract->start_date),
                                        'End Date' => dateFormat($contract->end_date),
                                        'Contract Period (years)' => $contract->period,
                                        'Per Schedule Period (months)' => $contract->schedule_period,
                                    ];
                                @endphp
                                <table class="table table-bordered table-sm mb-2">
                                    @foreach ($details as $key => $val)
                                        <tr>
                                            <th width="50%">{{ $key }}</th>
                                            <td>{{ $val }}</td>
                                        </tr>
                                    @endforeach
                                </table>

                                <ul class="nav nav-tabs nav-top-border no-hover-bg nav-justified" id="myTab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">
                                            Task Schedule
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">
                                            Equipments
                                        </a>
                                    </li>                    
                                </ul>
                                
                                <div class="tab-content px-1 p-1" id="myTabContent">
                                    <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                                        <div class="table-reponsive">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Title</th>
                                                        <th>Start Date</th>
                                                        <th>End Date</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>                                                
                                                    @foreach ($contract->task_schedules as $i => $row)                                                    
                                                        <tr>
                                                            <td>{{ $i+1 }}</td>
                                                            <td>{{ $row->title }}</td>
                                                            <td>{{ dateFormat($row->start_date) }}</td>
                                                            <td>{{ dateFormat($row->end_date) }}</td>
                                                            <td>{{ $row->status }}</td>
                                                        </tr>
                                                    @endforeach                                                    
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                                        <div class="table-reponsive">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Serial No</th>
                                                        <th>Type</th>
                                                        <th>Branch</th>
                                                        <th>Location</th>
                                                    </tr>
                                                </thead>
                                                <tbody>   
                                                    @php
                                                        $equipments = array();
                                                        foreach ($contract->contract_equipments as $row) {
                                                            $equipments[] = $row->equipment;
                                                        }
                                                    @endphp                                             
                                                    @foreach ($equipments as $i => $row)                                            
                                                        <tr>
                                                            <td>{{ $i+1 }}</td>
                                                            <td>{{ $row->unique_id }}</td>
                                                            <td>{{ $row->make_type }}</td>
                                                            <td>{{ $row->branch->name }}</td>
                                                            <td>{{ $row->location }}</td>
                                                        </tr>                                                        
                                                    @endforeach                                                    
                                                </tbody>
                                            </table>
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
</div>
@endsection