@extends ('core.layouts.app')

@section('title', 'Supplier Bill Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Supplier Bill Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.supplierbills.partials.supplierbills-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <table id="grnTbl" class="table table-striped table-bordered zero-configuration" width="100%" cellpadding="0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Bill No.</th>
                                <th>Supplier</th>
                                <th>Note</th>                                
                                <th>Amount</th>
                                <th>Balance</th>  
                                <th>Status</th>   
                                <th>Due Date</th>                                        
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
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    const config = {
        ajaxSetup: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        datepicker: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    const Index = {
        init() {
            this.drawDataTable();
        },

        drawDataTable() {
            $('#grnTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: "{{ route('biller.supplierbills.get') }}",
                    type: 'POST',
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    {data: 'tid', name: 'tid'},
                    {data: 'supplier', name: 'supplier'},
                    {data: 'note', name: 'note'},                    
                    {data: 'total', name: 'total'},
                    {data: 'balance', name: 'balance'},
                    {data: 'status', name: 'status'},
                    {data: 'due_date', name: 'due_date'},
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
