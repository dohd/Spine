@extends ('core.layouts.app')

@section ('title', trans('labels.backend.accounts.management'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Poject Gross Profit</h4>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <table id="projectsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Project No</th>  
                                        <th>Customer</th>
                                        <th>Title</th>   
                                        <th>Status</th>  
                                        <th>Quote/PI (Amount)</th>  
                                        <th>Income</th>    
                                        <th>Exp</th>   
                                        <th>G.P</th>                          
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
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}" }},
    };

    const Index = {
        init() {
            this.drawDataTable();
        },

        drawDataTable() {
            $('#projectsTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                stateSave: true,
                ajax: {
                    url: "{{ route('biller.accounts.get_project_gross_profit') }}",
                    type: 'post'
                },
                columns: [{
                        data: 'DT_Row_Index',
                        name: 'id'
                    },
                    {
                        data: 'tid',
                        name: 'tid'
                    },
                    {
                        data: 'customer',
                        name: 'customer'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'quote_amount',
                        name: 'quote_amount'
                    },
                    {
                        data: 'income',
                        name: 'income'
                    },
                    {
                        data: 'expense',
                        name: 'expense'
                    },
                    {
                        data: 'gross_profit',
                        name: 'gross_profit'
                    },
                ],
                order: [
                    [0, "desc"]
                ],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        }
    };

    $(() => Index.init());
</script>
@endsection