@extends ('core.layouts.app')

@section ('title', 'Asset Equipment Management')

@section('page-header')
    <h1>Asset Equipment Management </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class=" mb-0">Asset Equipment Management </h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.assetequipments.partials.assetequipments-header-buttons')
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
                                    <table id="assetequipments-table"
                                           class="table table-striped table-bordered zero-configuration" cellspacing="0"
                                           width="100%">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Item Name</th>
                                            <th>Serial</th>
                                            <th>Account Type</th>
                                            <th>Ledger Number</th>
                                            <th>Cost Price</th>
                                            <th>Qty</th>
                                            <th>{{ trans('general.createdat') }}</th>
                                            <th>{{ trans('labels.general.actions') }}</th>
                                        </tr>
                                        </thead>


                                        <tbody>
                                        <tr>
                                            <td colspan="100%" class="text-center text-success font-large-1"><i
                                                        class="fa fa-spinner spinner"></i></td>
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
    </div>
@endsection

@section('after-scripts')
    {{-- For DataTables --}}
    {{ Html::script(mix('js/dataTable.js')) }}

    <script>
        $(function () {
            setTimeout(function () {
                draw_data()
            }, {{config('master.delay')}});
        });

        function draw_data() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var dataTable = $('#assetequipments-table').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {
                    @lang('datatable.strings')
                },
                ajax: {
                    url: '{{ route("biller.assetequipments.get") }}',
                    type: 'post',
                    data: {c_type: 0}
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    {data: 'name', name: 'name'},
                    {data: 'serial', name: 'serial'},
                    {data: 'account_type', name: 'account_type'},
                    {data: 'account_id', name: 'account_id'},
                    {data: 'cost', name: 'cost'},
                    {data: 'qty', name: 'qty'},
                    {data: 'created_at', name: '{{config('module.assetequipments.table')}}.created_at'},
                    {data: 'actions', name: 'actions', searchable: false, sortable: false}
                ],
                order: [[0, "asc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: {
                    buttons: [

                        {extend: 'csv', footer: true, exportOptions: {columns: [0, 1, 2, 3, 4, 5, 6]}},
                        {extend: 'excel', footer: true, exportOptions: {columns: [0, 1, 2, 3, 4, 5, 6]}},
                        {extend: 'print', footer: true, exportOptions: {columns: [0, 1, 2, 3, 4, 5, 6]}}
                    ]
                }
            });
            $('#assetequipments-table_wrapper').removeClass('form-inline');

        }
    </script>
@endsection
