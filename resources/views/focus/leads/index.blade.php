@extends ('core.layouts.app')

@section ('title', 'Tickets Management')

@section('content')
<div>
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-2">
                <h4 class="content-header-title">Tickets Management</h4>
            </div>
            <div class="content-header-right col-md-6 col-12">
                <div class="media width-250 float-right mr-3">
                    <div class="media-body media-right text-right">
                        @include('focus.leads.partials.leads-header-buttons')
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
                                <table id="leads-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Ticket No</th>
                                            <th>Client & Branch</th>
                                            <th>Title</th>
                                            <th>New/Existing</th>
                                            <th>Source</th>
                                            <th>{{ trans('general.createdat') }}</th>
                                            <th>Client Ref</th>
                                            <th>{{ trans('labels.general.actions') }}</th>
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
</div>
@endsection

@section('after-scripts')
{{-- For DataTables --}}
{{ Html::script(mix('js/dataTable.js')) }}

<script>
    setTimeout(() => draw_data(), "{{ config('master.delay') }}");

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } });

    function draw_data() {
        const datatableLang = { @lang("datatable.strings") };
        var dataTable = $('#leads-table').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: datatableLang,
            ajax: {
                url: '{{ route("biller.leads.get") }}',
                type: 'post',
            },
            columns: [
                {
                    data: 'DT_Row_Index',
                    name: 'id'
                },
                {
                    data: 'reference',
                    name: 'reference'
                },
                {
                    data: 'client_name',
                    name: 'client_name'
                },
                {
                    data: 'title',
                    name: 'title'
                },
                {
                    data: 'client_status',
                    name: 'client_status'
                },
                {
                    data: 'source',
                    name: 'source'
                },
                {
                    data: 'created_at',
                    name: "{{ config('module.leads.table') }}.created_at"
                },
                {
                    data: 'client_ref',
                    name: 'client_ref'
                },
                {
                    data: 'actions',
                    name: 'actions',
                    searchable: false,
                    sortable: false
                }
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