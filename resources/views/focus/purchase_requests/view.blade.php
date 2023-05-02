@extends ('core.layouts.app')

@section('title', 'Purchase Requisition Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Purchase Requisition Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.purchase_requests.partials.purchase-request-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-header">
                    <a href="#" class="btn btn-warning btn-sm mr-1" data-toggle="modal" data-target="#statusModal">
                        <i class="fa fa-pencil" aria-hidden="true"></i> Status
                    </a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        @php 
                            $req = $purchase_request;                        
                            $details = [
                                'Requisition No.' => gen4tid('REQ-', $req->tid),
                                'Status' => $req->status,
                                'Priority' => $req->priority,
                                'Date' => dateFormat($req->date),
                                'Employee' => $req->employee? $req->employee->full_name : '',
                                'Expected Delivery Date' => dateFormat($req->expect_date),
                                'Remark' => $req->note,
                                'Item List Description' => $req->item_descr,
                            ];
                        @endphp
                        @foreach ($details as $key => $val)
                            <tr>
                                <th width="30%">{{ $key }}</th>
                                <td>
                                    @if (in_array($key, ['Status', 'Priority']))
                                        <span class="font-weight-bold">{{ $val }}</span>
                                    @elseif ($key == 'Item List Description')
                                        {!! $val !!}
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
@include('focus.purchase_requests.partials.status-modal')
@endsection
