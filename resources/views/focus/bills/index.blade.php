@extends('core.layouts.app')

@section('title', 'Bills Management')

@section('content')
<div>
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-2">
                <h4 class="content-header-title">Bills Management</h4>
            </div>
            <div class="content-header-right col-md-6 col-12">
                <div class="media width-250 float-right mr-3">
                    <div class="media-body media-right text-right">
                        @include('focus.bills.partials.bills-header-buttons')
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="card">
                <div class="card-content">
                    <div class="card-body">
                        <table id="billsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Supplier</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Paid</th>
                                    <th>Document</th>
                                    <th>Date</th>
                                    <th>Due Date</th>                                            
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="100%" class="text-center text-success font-large-1">
                                        <i class="fa fa-spinner spinner"></i>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } });
    setTimeout(() => draw_data(), "{{ config('master.delay') }}");

    function draw_data() {
        const language = {@lang("datatable.strings")};
        const dataTable = $('#billsTbl').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language,
            ajax: {
                url: "{{ route('biller.bills.get') }}",
                type: 'post',
            },
            columns: [
                {
                    data: 'DT_Row_Index',
                    name: 'id'
                },
                {
                    data: 'supplier',
                    name: 'supplier'
                },
                {
                    data: 'amount',
                    name: 'amount'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'paid',
                    name: 'paid'
                },
                {
                    data: 'document',
                    name: 'document'
                },
                {
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'due_date',
                    name: 'due_date'
                },
                // {
                //     data: 'actions',
                //     name: 'actions',
                //     searchable: false,
                //     sortable: false
                // }
            ],
            order: [[0, "desc"]],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: {
                buttons: [
                    {
                        extend: 'csv',
                        footer: true,
                        exportOptions: {
                            columns: [0, 1]
                        }
                    },
                    {
                        extend: 'excel',
                        footer: true,
                        exportOptions: {
                            columns: [0, 1]
                        }
                    },
                    {
                        extend: 'print',
                        footer: true,
                        exportOptions: {
                            columns: [0, 1]
                        }
                    }
                ]
            }
        });
    }
</script>
@endsection
