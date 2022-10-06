@extends('core.layouts.app')

@section('title', 'PM Report Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">PM Report Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.contractservices.partials.contractservices-header-buttons')
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
                            <table id="serviceTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Customer - Branch</th>
                                        <th>Contract - Schedule</th>
                                        <th>Bill Amount</th>
                                        <th>Unit Count</th>
                                        <th>Jobcard No</th>
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
    </div>
</div>
@endsection

@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('focus/js/select2.min.js') }}
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });
    setTimeout(() => draw_data(), "{{ config('master.delay') }}");

    function draw_data() {
        const language = {
            @lang("datatable.strings")
        };
        const dataTable = $('#serviceTbl').dataTable({
            processing: true,
            responsive: true,
            language,
            ajax: {
                url: '{{ route("biller.contractservices.get") }}',
                type: 'POST',
            },
            columns: [{
                    data: 'DT_Row_Index',
                    name: 'id'
                },
                {
                    data: 'client',
                    name: 'client'
                },
                {
                    data: 'contract',
                    name: 'contract'
                },
                {
                    data: 'bill',
                    name: 'bill'
                },
                {
                    data: 'unit',
                    name: 'unit'
                },
                {
                    data: 'jobcard_no',
                    name: 'jobcard_no'
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
                { type: "custom-number-sort", targets: [3] },
                { type: "custom-date-sort", targets: [6] }
            ],
            order: [[0, "desc"]],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }
</script>
@endsection