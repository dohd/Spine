@extends('core.layouts.app')

@section('title', 'View | Schedule Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Schedule Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                @include('focus.taskschedules.partials.taskschedule-header-buttons')
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
                                $contract = $taskschedule->contract;
                                $contract_title = $contract? $contract->title : '';
                                $customer_title = $contract->customer? $contract->customer->company : ''; 
                                
                                $details = [
                                    'Schedule Title' => $taskschedule->title,
                                    'Equipments' => '',
                                    'Customer Contract' => "{$contract_title} - {$customer_title}", 
                                    'Schedule Date (Start - End)' => dateFormat($taskschedule->start_date) . ' : ' . dateFormat($taskschedule->end_date),
                                    'Actual Date (Start - End)' => dateFormat($taskschedule->actual_startdate) . ' : ' . dateFormat($taskschedule->actual_enddate),
                                    'Service Rate' => numberFormat($taskschedule->equipments->sum('service_rate'))
                                ];
                            @endphp
                            <table class="table table-bordered table-sm mb-2">
                                @foreach ($details as $key => $val)
                                    <tr>
                                        <th width="50%">{{ $key }}</th>
                                        <td>
                                            @if ($key == 'Equipments')
                                                <a href="#" class="btn btn-warning btn-sm mr-1" data-toggle="modal" data-target="{{ $taskschedule->equipments->count()? '#statusModal' : '' }}">
                                                    <i class="fa fa-clone" aria-hidden="true"></i> Copy
                                                </a>      
                                                <a class="btn btn-purple btn-sm" href="{{ route('biller.equipments.index', ['customer_id' => $contract->customer_id, 'schedule_id' => $taskschedule->id]) }}" title="equipments">
                                                    <i class="fa fa-list"></i> List
                                                </a>  
                                            @else
                                                {{ $val }}
                                            @endif
                                        </td>
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
@include('focus.taskschedules.partials.copy_modal')
@endsection
