@extends('core.layouts.app')

@section('title', $is_debit ? 'Debit Notes Management' : 'Credit Notes Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">{{ $is_debit ? 'Debit Notes Management' : 'Credit Notes Management' }}</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.creditnotes.partials.creditnotes-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <table id="creditnotesTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>#{{ $is_debit? 'DN' : 'CN' }} No</th>
                                <th>Customer</th>
                                <th>#Invoice No</th>
                                <th>Amount</th>
                                <th>Date</th>  
                                <th>Action</th>                                                                           
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
@endsection

@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } });
    setTimeout(() => draw_data(), "{{ config('master.delay') }}");

    function draw_data() {
        const is_debit = "{{ $is_debit ? 1 : 0 }}";
        const language = {@lang("datatable.strings")};
        const dataTable = $('#creditnotesTbl').dataTable({
            processing: true,
            responsive: true,
            language,
            ajax: {
                url: "{{ route('biller.creditnotes.get') }}",
                type: 'post',
                data: {is_debit}
            },
            columns: [
                {
                    data: 'DT_Row_Index',
                    name: 'id'
                },
                {
                    data: 'tid',
                    name: 'tid'
                },
                {
                    data: 'customer', 
                    name: 'customer',
                },
                {
                    data: 'invoice_no',
                    name: 'invoice_no'
                },
                {
                    data: 'amount',
                    name: 'amount'
                },
                {
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'actions',
                    name: 'actions',
                    searchable: false,
                    sortable: false
                }            
            ],
            columnDefs: [
                { type: "custom-number-sort", targets: [4] },
                { type: "custom-date-sort", targets: [5] }
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