@extends ('core.layouts.app')

@section('title', 'Product Refill Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Product Refill Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.product_refills.partials.refill-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-header"></div>
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        @php
                            $details = [
                                'Employee' => $employee_name,
                                'Leave Category' => $leave->leave_category? $leave->leave_category->title : '',
                                'Leave Status' => $leave->status,
                                'Leave Reason' => $leave->reason,
                                'Leave Duration' => $leave->qty . ' days',
                                'Start Date' => dateFormat($leave->start_date),
                                'End Date' => dateFormat($leave->end_date),
                            ];
                        @endphp
                        @foreach ([] as $key => $val)
                            <tr>
                                <th width="30%">{{ $key }}</th>
                                <td>
                                    {{ $val }}
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
