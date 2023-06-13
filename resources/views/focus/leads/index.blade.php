@extends ('core.layouts.app')

@section ('title', 'Tickets Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Tickets Management</h4>
        </div>
        <div class="content-header-right col-6">
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
                    <div class="card-body">
                        <div class="row no-gutters">
                            <div class="col-sm-3 col-md-2 h4">Open Tickets</div>
                            <div class="col-sm-2 col-md-1 h4 text-primary font-weight-bold">{{ $open_lead }}</div>
                            <div class="col-sm-12 col-md-1 h4 text-primary font-weight-bold">{{ numberFormat(div_num($open_lead, $total_lead) * 100) }}%</div>
                        </div>
                        <div class="row no-gutters">
                            <div class="col-sm-3 col-md-2 h4">Closed Tickets</div>
                            <div class="col-sm-2 col-md-1 h4 text-success font-weight-bold">{{ $closed_lead }}</div>
                            <div class="col-sm-12 col-md-1 h4 text-success font-weight-bold">{{ numberFormat(div_num($closed_lead, $total_lead) * 100) }}%</div>
                        </div>
                    </div>
                </div>
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
                                        <th>Days to Event</th>
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

@endsection

@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    setTimeout(() => draw_data(), "{{ config('master.delay') }}");
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } });

    function draw_data() {
        const dataTable = $('#leads-table').dataTable({
            stateSave: true,
            processing: true,
            responsive: true,
            language: {@lang("datatable.strings")},
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
                    name: 'created_at',
                },
                {
                    data: 'client_ref',
                    name: 'client_ref'
                },
                {
                    data: 'exact_date',
                    name: 'exact_date'
                },
                {
                    data: 'actions',
                    name: 'actions',
                    searchable: false,
                    sortable: false
                }
            ],
            columnDefs: [
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