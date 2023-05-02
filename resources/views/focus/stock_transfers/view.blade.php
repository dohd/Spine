@extends ('core.layouts.app')

@section('title', 'View | Stock Transfer Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Stock Transfer Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.stock_transfers.partials.stock-transfer-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-header">
                    {{-- <a href="#" class="btn btn-warning btn-sm mr-1" data-toggle="modal" data-target="#Stock TransferStatusModal">
                        <i class="fa fa-pencil" aria-hidden="true"></i> Status
                    </a> --}}
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        @php
                            $details = [
                                'Stock Transfer Category' => '',
                                'Stock Transfer Status' => $stock_transfer->status,
                                'Stock Transfer Reason' => $stock_transfer->reason,
                                'Stock Transfer Duration' => $stock_transfer->qty . ' days',
                                'Start Date' => dateFormat($stock_transfer->start_date),
                                'End Date' => dateFormat($stock_transfer->end_date),
                            ];
                        @endphp
                        @foreach ($details as $key => $val)
                            <tr>
                                <th width="30%">{{ $key }}</th>
                                <td>
                                    @if ($key == 'Stock Transfer Status')
                                        <span class="text-success">{{ $val }}</span>
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
@endsection
