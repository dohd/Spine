@extends ('core.layouts.app')

@section ('title', trans('labels.backend.accounts.management'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Project Gross Profit</h4>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">                            
                            <div class="col-4">
                                <select class="form-control select2" id="customerFilter" data-placeholder="Search Customer">
                                    <option value=""></option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->company }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-3">
                                <select class="form-control select2" id="branchFilter" data-placeholder="Search Branch">
                                    <option value=""></option>
                                    @foreach ([] as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>                            
                            <div class="col-2">
                                <select name="status" id="status" class="custom-select">
                                    <option value="">-- Select Project Status --</option>
                                    @foreach (['active', 'complete'] as $val)
                                        <option value="{{ $val }}">{{ ucfirst($val) }}</option>
                                    @endforeach
                                </select>
                            </div>
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
                            <div class="row">
                                <div class="col-2">{{ trans('general.search_date')}} </div>
                                <div class="col-2">
                                    <input type="text" name="start_date" id="start_date" class="form-control datepicker date30  form-control-sm" autocomplete="off" />
                                </div>
                                <div class="col-2">
                                    <input type="text" name="end_date" id="end_date" class="form-control datepicker form-control-sm" autocomplete="off" />
                                </div>
                                <div class="col-2">
                                    <input type="button" name="search" id="search" value="Search" class="btn btn-info btn-sm" />
                                </div>
                            </div>
                            <hr>
                            <table id="projectsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Project No</th>  
                                        <th>Client-Branch</th>
                                        <th>Title</th>   
                                        <th>QT/PI Amount</th>  
                                        <th>Verification</th>
                                        <th>Income</th>    
                                        <th>Expense</th>   
                                        <th>G.P</th>   
                                        <th>%P</th>                       
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
{{ Html::script('focus/js/select2.min.js') }}
<script>
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}" }},
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
        branchSelect: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.branches.select') }}",
                dataType: 'json',
                type: 'POST',
                data: ({term}) => ({search: term, customer_id: $("#customerFilter").val()}),
                processResults: (data) => {
                    return { results: data.map(v => ({text: v.name, id: v.id})) };
                },
            }
        }
    };

    const Index = {
        startDate: '',
        endDate: '',

        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            this.drawDataTable();

            $('#status').change(this.statusChange);
            $('#search').click(this.searchDateClick);
            $("#customerFilter").select2({allowClear: true}).change(Index.onChangeCustomer);
            $("#branchFilter").select2(config.branchSelect).change(Index.onChangeBranch);
        },

        statusChange() {
            $('#projectsTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        searchDateClick() {
            if (!$('#start_date').val() || !$('#end_date').val()) 
                return alert("Start-End Date range required!"); 
            Index.startDate = $('#start_date').val();
            Index.endDate = $('#end_date').val();
            $('#projectsTbl').DataTable().destroy();
            Index.drawDataTable();
        },

        onChangeCustomer() {
            $("#branchFilter option:not(:eq(0))").remove();
            $('#projectsTbl').DataTable().destroy();
            Index.drawDataTable();
        },

        onChangeBranch() {
            $('#projectsTbl').DataTable().destroy();
            Index.drawDataTable(); 
        },

        drawDataTable() {
            $('#projectsTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                stateSave: true,
                ajax: {
                    url: "{{ route('biller.accounts.get_project_gross_profit') }}",
                    type: 'post',
                    data: {
                        status: $('#status').val(),
                        start_date: this.startDate,
                        end_date: this.endDate,                                  
                        customer_id: $("#customerFilter").val(),
                        branch_id: $("#branchFilter").val(),
                    }
                },
                columns: [{data: 'DT_Row_Index',name: 'id'},
                    ...[
                        'tid', 'customer', 'name', 'quote_amount', 'verify_date', 'income', 'expense',
                        'gross_profit', 'percent_profit',
                    ].map(v => ({data:v, name:v})),
                ],
                columnDefs: [
                    { type: "custom-number-sort", targets: [6, 7, 8, 9] },
                    { type: "custom-date-sort", targets: [5] }
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        },
    }

    $(() => Index.init());
</script>
@endsection