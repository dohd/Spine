@extends ('core.layouts.app')

@section ('title', 'Surcharges Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class=" mb-0">Surcharges Management </h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    {{-- @include('focus.surcharges.partials.surcharges-header-buttons') --}}
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
                            <table id="surcharges-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#Id</th>
                                        <th>Employee Name</th>
                                        <th>Category Issue</th>
                                        <th>Cost</th>
                                        <th>{{ trans('labels.general.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="100%" class="text-center text-success font-large-1"><i class="fa fa-spinner spinner"></i></td>
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

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

    function draw_data() {
        const tableLan = {@lang('datatable.strings')};
        var dataTable = $('#surcharges-table').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: tableLan,
            ajax: {
                url: '{{ route("biller.surcharges.get") }}',
                type: 'POST',
                data: { c_type: 0 }
            },
            columns: [
                {
                    data: 'employee_id',
                    name: 'employee_id'
                },
                {
                    data: 'employee_name',
                    name: 'employee_name'
                },
                {
                    data: 'issue_type',
                    name: 'issue_type'
                },
                {
                    data: 'cost',
                    name: 'cost'
                },
                {
                    data: 'actions',
                    name: 'actions',
                    searchable: false,
                    sortable: false
                }
            ],
            order: [
                [0, "desc"]
            ],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }
</script>
@endsection