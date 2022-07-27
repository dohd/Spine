@extends ('core.layouts.app')

@section ('title', trans('labels.backend.accounts.management'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">{{ trans('labels.backend.accounts.management') }}</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.accounts.partials.accounts-header-buttons')
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
                            <table id="accounts-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>System</th>  
                                        <th>#Account No</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Debit</th>
                                        <th>Credit</th> 
                                        <th>Balance</th>   
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
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });
    setTimeout(() => draw_data(), "{{ config('master.delay') }}")
            
    function draw_data() {
        var dataTable = $('#accounts-table').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            stateSave: true,
            ajax: {
                url: "{{ route('biller.accounts.get') }}",
                type: 'post'
            },
            columns: [{
                    data: 'DT_Row_Index',
                    name: 'id'
                },
                {
                    data: 'system_type',
                    name: 'system_type'
                },
                {
                    data: 'number',
                    name: 'number'
                },
                {
                    data: 'holder',
                    name: 'holder'
                },
                {
                    data: 'account_type',
                    name: 'account_type'
                },
                {
                    data: 'debit',
                    name: 'debit'
                },
                {
                    data: 'credit',
                    name: 'credit'
                },
                {
                    data: 'balance',
                    name: 'balance'
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