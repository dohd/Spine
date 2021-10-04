@extends ('core.layouts.app')

@section ('title', 'Equipment Management')

@section('page-header')
    <h1>Equipment Management</h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class=" mb-0">Equipment Management</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.equipments.partials.equipments-header-buttons')
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
                                    <table id="equipments-table"
                                           class="table table-striped table-bordered zero-configuration" cellspacing="0"
                                           width="100%">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                             <th>ID</th>
                                            <th>Client</th>
                                            <th>Serial</th>
                                            <th>Manufacturer</th>
                                            <th>Model</th>
                                            <th>Location</th>
                                            <th>Type</th>
                                            <th>Related</th>
                                            <th>Last Maint </th>
                                            <th>Next Maint</th>
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

            var dataTable = $('#equipments-table').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {
                    @lang('datatable.strings')
                },
                ajax: {
                    url: '{{ route("biller.equipments.get") }}',
                    type: 'post',
                    data: {c_type: 0}
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    {data: 'unique_id', name: 'unique_id'},
                    {data: 'customer', name: 'customer'},
                    {data: 'equip_serial', name: 'equip_serial'},
                    {data: 'manufacturer', name: 'manufacturer'},
                    {data: 'model', name: 'model'},
                    {data: 'location', name: 'location'},
                    {data: 'unit_type', name: 'unit_type'},
                    {data: 'relationship', name: 'relationship'},
                    {data: 'last_maint_date', name: 'last_maint_date'},
                    {data: 'next_maintenance_date', name: 'next_maintenance_date'},
                     
                    {data: 'created_at', name: '{{config('module.branches.table')}}.created_at'},
                    {data: 'actions', name: 'actions', searchable: false, sortable: false}
                ],
                order: [[0, "asc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: {
                    buttons: [

                        {extend: 'csv', footer: true, exportOptions: {columns: [0, 1]}},
                        {extend: 'excel', footer: true, exportOptions: {columns: [0, 1]}},
                        {extend: 'print', footer: true, exportOptions: {columns: [0, 1]}}
                    ]
                }
            });
            $('#Equipments-table_wrapper').removeClass('form-inline');

        }
    </script>
@endsection
