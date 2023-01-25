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
                            <div class="col-2">
                                <label for="status">Project Status</label>
                                <select name="status" id="status" class="custom-select">
                                    <option value="">-- select status --</option>
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
<script>
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}" }},
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true}
    };

    const Index = {
        status: '',
        startDate: '',
        endDate: '',

        init() {
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            this.drawDataTable();
            $('#status').change(this.statusChange);
            $('#search').click(this.searchDateClick);
        },

        statusChange() {
            Index.status = $(this).val();
            $('#projectsTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        searchDateClick() {
            const startDate = $('#start_date').val();
            const endDate = $('#end_date').val();
            const verifyState = $('#verify_state').val();
            if (!startDate || !endDate) return alert("Date range required!"); 

            Index.startDate = startDate;
            Index.endDate = endDate;
            $('#projectsTbl').DataTable().destroy();
            return Index.drawDataTable();
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
                        status: this.status,
                        start_date: this.startDate,
                        end_date: this.endDate
                    }
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
                        data: 'quote_amount',
                        name: 'quote_amount'
                    },
                    {
                        data: 'verify_date',
                        name: 'verify_date'
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
                    {
                        data: 'percent_profit',
                        name: 'percent_profit'
                    },
                ],
                columnDefs: [
                    { type: "custom-number-sort", targets: [6, 7, 8, 9] },
                    { type: "custom-date-sort", targets: [5] }
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