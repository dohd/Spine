@extends ('core.layouts.app')

@section('title', 'Project Stock Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Project Stock Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.projectstock.partials.projectstock-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">  
                    <table id="quotesTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>   
                                <th>Quote / PI No</th>
                                <th>Customer & Branch</th>   
                                <th>Title</th> 
                                <th>Product Count</th>  
                                <th>Issued Count</th>                                                                    
                                <th>Status</th>
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

    {{ Form::open(['route' => 'biller.projectstock.create', 'method' => 'GET']) }}
        {{ Form::hidden('quote_id', null, ['id' => 'quote']) }}
    {{ Form::close() }}
</div>
@endsection

@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    const config = {
        ajaxSetup: {headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }},
        datepicker: {format: "{{ config('core.user_date_format') }}", autoHide: true}
    };

    const Index = {
        init(config) {
            $('.datepicker').datepicker(config.datepicker).datepicker('setDate', new Date());
            this.drawDataTable();
            $('#quotesTbl').on('change', '.select-row', this.selectRow);
        },

        selectRow() {
            const el = $(this);
            if (el.prop('checked')) {
                $('#quote').val(el.val());
                $('#quotesTbl tbody tr').each(function() {
                    const row = $(this);
                    if (row.find('.select-row').val() != el.val()) {
                        row.find('.select-row').prop('checked', false);
                    }
                });
            } else {
                $('#quote').val('');
                $('#quotesTbl tbody tr').each(function() {
                    const row = $(this);
                    row.find('.select-row').prop('checked', false);
                });
            }
            if ($('#quote').val()) {
                swal({
                    title: 'Proceed to Issue Stock?',
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                    showCancelButton: true,
                }, () => $('form').submit()); 
            }
        },

        drawDataTable() {
            $('#quotesTbl').dataTable({
                processing: true,
                responsive: true,
                stateSave: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: "{{ route('biller.projectstock.get_quote') }}",
                    type: 'POST',
                },
                columns: [
                    {
                        data: 'checkbox',
                        searchable: false, 
                        sortable: false
                    },
                    {
                        data: 'date',
                        name: 'date'
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
                        data: 'notes',
                        name: 'notes'
                    },    
                    {
                        data: 'item_count',
                        name: 'item_count'
                    },    
                    {
                        data: 'issue_count',
                        name: 'issue_count'
                    },    
                    {
                        data: 'issue_status',
                        name: 'issue_status'
                    },                
                ],
                columnDefs: [
                    { type: "custom-date-sort", targets: 1 }
                ],
                order:[[0, 'desc']],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'] 
            });
        }
    };

    $(() => Index.init(config));
</script>
@endsection