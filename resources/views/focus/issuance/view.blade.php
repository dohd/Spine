@extends('core.layouts.app')

@section('title',  'Issuance Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Issuance Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.issuance.partials.issuance-header-buttons')
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
                            <h5>{{ $issuance->quote->bank_id? 'PI' : 'Quote' }}: <b>{{ gen4tid('', $issuance->quote->tid) }}</b></h5>
                            <h5>
                                Issuance Status: <b>{{ strtoupper($issuance->quote->issuance_status) }}</b>
                                <a href="javascript:" class="btn btn-success btn-sm" data-toggle="modal" data-target="#statusModal">
                                    <i class="fa fa-pencil"></i>
                                </a>
                            </h5>
                            <div class="table-responsive mt-1"> 
                                <table id="issuaceTbl" class="tfr my_stripe_single text-center" width="60%">
                                    <thead>
                                        <tr class="bg-gradient-directional-blue white">
                                            <th>Date</th>
                                            <th>Note</th>
                                            <th>Amount (Ksh.)</th>
                                            <th>Tool Requisition</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($issuance->quote->issuance as $item)
                                            <tr>
                                                <td>{{ dateFormat($item->date) }}</td>
                                                <td><a href="javascript: getItems({{ $item->id }}); void(0);">
                                                    {{ $item->note }}</a>
                                                </td>
                                                <td>{{ number_format($item->total, 2) }}</td>
                                                <td>{{ $item->ref }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="table-responsive mt-1">
                                <table id="itemTbl" class="table table-xs text-center">
                                    <thead>
                                        <tr class="bg-gradient-directional-blue white">
                                            <th>Product Name</th>
                                            <th>Issued Qty</th>
                                            <th>Requisition</th>
                                            <th>Warehouse</th>
                                        </tr>
                                    </thead>
                                    <tbody>  
                                        @foreach ($issuance_items as $item)
                                            <tr>
                                                <td>{{ $item->product->name }}</td>
                                                <td>{{ $item->qty }}</td>
                                                <td>{{ $item->ref }}</td>
                                                <td>{{ $item->warehouse->title }}</td>
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
@include('focus.issuance.partials.status-modal')
@endsection

@section("after-scripts")
<script type="text/javascript">
    $.ajaxSetup({headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }});

    $('#issuaceTbl').on('click', 'note', function() {
        console.log('note')
        const row = $(this).parents('tr');
    });

    function itemRow(v) {
        return `
            <tr>
                <td>${v.product.name}</td>
                <td>${v.qty}</td>
                <td>${v.ref}</td>
                <td>${v.warehouse.title}</td>
            </tr>
        `;
    }
    function getItems(id) {
        $.ajax({
            url: "{{ route('biller.issuance.get_items') }}?id=" + id,
            success: data => {
                $('#itemTbl tbody tr').remove();
                data.forEach(v => $('#itemTbl tbody').append(itemRow(v)));
            }
        });
    }
</script>
@endsection