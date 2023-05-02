@extends ('core.layouts.app')

@section('title', 'Attendance Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Attendance Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.attendances.partials.attendances-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                {{-- <div class="card-header">
                    <a href="#" class="btn btn-warning btn-sm mr-1" data-toggle="modal" data-target="#leaveStatusModal">
                        <i class="fa fa-pencil" aria-hidden="true"></i> Status
                    </a>
                </div> --}}
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        @php
                            $employee_name = '';
                            $employee = $attendance->employee;
                            if ($employee) $employee_name = $employee->first_name . ' ' . $employee->last_name;
                        
                            $details = [
                                'Date' => dateFormat($attendance->date),
                                'Employee' => $employee_name,
                                'Clock In' => $attendance->clock_in,
                                'Clock Out' => $attendance->clock_out,
                                'Hours' => +$attendance->hrs,
                                'Attendance Status' => $attendance->status,
                            ];
                        @endphp
                        @foreach ($details as $key => $val)
                            <tr>
                                <th>{{ $key }}</th>
                                <td>{{ $val }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- @include('focus.leave.partials.leave-status-modal') --}}
@endsection
