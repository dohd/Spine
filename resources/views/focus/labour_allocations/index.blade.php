@extends ('core.layouts.app')

@section('title', 'Labour Allocation Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Labour Allocation Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.labour_allocations.partials.labour_allocation-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="col-md-4">
                            <select name="client_id" class="custom-select" id="client" data-placeholder="Search Customer">
                                <option value=""></option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->company }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <table id="labour_allocationsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>#Project No</th>
                                        <th>Project Title</th>
                                        <th>#QT/PI No</th>
                                        <th>Customer - Branch</th>
                                        <th>Employees</th>
                                        <th>Hours</th>
                                        <th>Job Type</th>
                                        <th>Note</th>
                                        <th>Job Card</th>
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
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    const Index = {
        init() {
             $('#client').select2({allowClear: true});
             $('#client').change(Index.onChangeClient);
             this.drawDataTable();
        },
        
        onChangeClient() {
            $('#labour_allocationsTbl').DataTable().destroy();
            return Index.drawDataTable();   
        },

        drawDataTable() {
            $('#labour_allocationsTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: "{{ route('biller.labour_allocations.get') }}",
                    type: 'POST',
                     data: {
                        client_id: $('#client').val(),
                    },
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    {data: 'date', name: 'date'},
                    {data: 'tid', tid: 'tid'},
                    {data: 'project_name', name: 'project_name'},
                    {data: 'quote_tid', name: 'quote_tid'},
                    {data: 'customer_branch', name: 'customer_branch'},
                    {data: 'employee_name', name: 'employee_name'},
                    {data: 'hrs', name: 'hrs'},
                    {data: 'type', name: 'type'},
                    {data: 'note', name: 'note'},
                    {data: 'job_card', name: 'job_card'},
                    {data: 'actions', name: 'actions', searchable: false, sortable: false}
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        }
    };

    $(() => Index.init());
</script>
@endsection
