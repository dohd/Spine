@extends ('core.layouts.app')

@section ('title', 'Equipment Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Equipment Management</h4>
        </div>
        <div class="content-header-right col-6">
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
                            <div class="row">
                                <div class="col-4">
                                    <label for="customer">Customer</label>
                                    <select name="customer" class="form-control" id="customer" data-placeholder="Choose customer"></select>
                                </div>
                                <div class="col-4">
                                    <label for="branch">Branch</label>
                                    <select name="branch" class="form-control" id="branch" data-placeholder="Choose customer"></select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <table id="equipTbl" class="table table-striped table-bordered zero-configuration" width="100%" cellpadding="0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>System ID</th>
                                        <th>Tag ID</th>
                                        <th>Branch</th>
                                        <th>Type</th>
                                        <th>Size</th>
                                        <th>Make</th>
                                        <th>Location</th>                                        
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
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } });
    setTimeout(() => draw_data(), "{{ config('master.delay') }}");

    // select2 config
    function select2Config(url, callback, extraData) {
        return {
            ajax: {
                url,
                dataType: 'json',
                type: 'POST',
                data: ({term}) => ({search: term, ...extraData}),
                quietMillis: 50,
                processResults: callback
            }
        }
    }

    const customerUrl = "{{ route('biller.customers.select') }}";
    const customerCb = data => ({ results: data.map(v => ({id: v.id, text: v.name + ' - ' + v.company})) });
    $('#customer').select2(select2Config(customerUrl, customerCb));

    const branchUrl = "{{ route('biller.branches.select') }}";
    const branchCb = data => ({ results: data.map(v => ({id: v.id, text: v.name})) });
    $('#branch').select2();

    // on change customer and branch 
    $(document).on('change', '#customer, #branch', function() {
        if ($(this).is('#customer')) {
            const customer_id = $(this).val();
            $('#branch').select2(select2Config(branchUrl, branchCb, {customer_id}));
        }

        $('#equipTbl tbody tr:first').remove();
        $('#equipTbl').DataTable().destroy();
        draw_data($('#customer').val(), $('#branch').val());
    });

    function draw_data(customer_id='', branch_id='') {
        const language = {@lang('datatable.strings')};
        const dataTable = $('#equipTbl').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language,
            ajax: {
                url: "{{ route('biller.equipments.get') }}",
                type: 'POST',
                data: {customer_id, branch_id},
            },
            columns: [
                {data: 'DT_Row_Index', name: 'id'},
                {data: 'tid', name: 'tid'},
                {data: 'unique_id', name: 'unique_id'},
                {data: 'branch', name: 'branch'},
                {data: 'unit_type', name: 'unit_type'},
                {data: 'capacity', name: 'capacity'},
                {data: 'manufacturer', name: 'manufacturer'},
                {data: 'location', name: 'location'},
                {data: 'actions', name: 'actions', searchable: false, sortable: false}
            ],
            order: [[0, "asc"]],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }
</script>
@endsection
