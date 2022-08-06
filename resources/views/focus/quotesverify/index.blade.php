@extends ('core.layouts.app')

@section ('title', 'Verification Management')

@section('content')
<div>
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-2">
                <h4 class="content-header-title">Verification Management</h4>
            </div>   
            <div class="content-header-right col-md-6 col-12">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        <div class="btn-group">
                            <a href="{{ route('biller.rjcs.index') }}" class="btn btn-success">
                                <i class="fa fa-list-alt"></i> Rjc
                            </a>                         
                        </div>
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
                                    <div class="col-2">
                                        <select name="verify_state" id="verify_state" class="custom-select">
                                            <option value="">-- Verification State--</option>
                                            @foreach (['yes' => 'verified', 'no' => 'unverified'] as $key => $val)
                                                <option value="{{ ucfirst($key) }}">{{ ucfirst($val) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <br>
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
                                <table id="quotesTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ trans('customers.customer') }}</th>
                                            <th># Quote / PI</th>
                                            <th>Title</th>                                            
                                            <th>{{ trans('general.amount') }} (Ksh.)</th>
                                            <th>Verified (Ksh.)</th>
                                            <th>Project No</th>
                                            <th>LPO No</th>
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
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    const config = {
        ajaxSetup: {
            headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }
        },
        datepicker: {format: "{{ config('core.user_date_format') }}", autoHide: true}
    };

    const Index = {
        init(config) {
            $.ajaxSetup(config.ajaxSetup);
            $('.datepicker').datepicker(config.datepicker).datepicker('setDate', new Date());

            $('#verify_state').change(this.verifyStateChange);
            $('#search').click(this.searchDateClick);
            this.drawDataTable();
        },

        verifyStateChange() {
            const el = $(this);
            $('#quotesTbl').DataTable().destroy();
            return Index.drawDataTable({verify_state: el.val()});
        },

        searchDateClick() {
            const start_date = $('#start_date').val();
            const end_date = $('#end_date').val();
            if (start_date && end_date) {
                $('#quotesTbl').DataTable().destroy();
                return Index.drawDataTable({start_date, end_date});
            } 
            alert("Date range required!");    
        },

        drawDataTable(params={}) {
            $('#quotesTbl').dataTable({
                processing: true,
                responsive: true,
                stateSave: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: '{{ route("biller.quotes.get_project") }}',
                    type: 'post',
                    data: {
                        ...params,
                        pi_page: location.href.includes('page=pi') ? 1 : 0
                    },
                },
                columns: [{
                        data: 'DT_Row_Index',
                        name: 'id'
                    },
                    {
                        data: 'customer',
                        name: 'customer'
                    },
                    {
                        data: 'tid',
                        name: 'tid'
                    },
                    {
                        data: 'notes',
                        name: 'notes'
                    },
                    {
                        data: 'total',
                        name: 'total'
                    },
                    {
                        data: 'verified_total',
                        name: 'verified_total'
                    },
                    {
                        data: 'project_tid',
                        name: 'project_tid'
                    },
                    {
                        data: 'lpo_number',
                        name: 'lpo_number'
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
                columnDefs: [
                    { type: "custom-number-sort", targets: [4, 5] },
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: [ 'csv', 'excel', 'print']
            });
        }
    };

    $(() => Index.init(config));
</script>
@endsection