@extends('core.layouts.app')

@section('title', 'Schedule Management')

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
                            <table id="scheduleTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Client Contract</th>
                                        <th>Title</th>
                                        <th>Unit Service Status</th>
                                        <th>Total Service Amount</th>
                                        <th>Total Bill Amount</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
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
    </div>
</div>
@endsection

@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('focus/js/select2.min.js') }}
<script>
$.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});
    setTimeout(() => draw_data(), "{{ config('master.delay') }}");

    function draw_data() {
        const language = { @lang("datatable.strings") };
        const dataTable = $('#scheduleTbl').dataTable({
            processing: true,
            responsive: true,
            language,
            ajax: {
                url: '{{ route("biller.taskschedules.get") }}',
                type: 'POST',
            },
            columns: [
                {
                    data: 'DT_Row_Index',
                    name: 'id'
                },
                {
                    data: 'contract',
                    name: 'contract'
                },
                {
                    data: 'title',
                    name: 'title'
                },
                {
                    data: 'loaded',
                    name: 'loaded'
                },
                {
                    data: 'total_rate',
                    name: 'total_rate'
                },
                {
                    data: 'total_charged',
                    name: 'total_charged'
                },
                {
                    data: 'start_date',
                    name: 'start_date'
                },
                {
                    data: 'end_date',
                    name: 'end_date'
                },
                {
                    data: 'actions',
                    name: 'actions',
                    searchable: false,
                    sortable: false
                }
            ],
            columnDefs: [
                { type: "custom-number-sort", targets: [5, 6] },
                { type: "custom-date-sort", targets: [7, 8] }
            ],
            order: [[0, "desc"]],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons:  [ 'csv', 'excel', 'print'],
        });
    }        
</script>
@endsection
