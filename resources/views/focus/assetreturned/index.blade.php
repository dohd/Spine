@extends ('core.layouts.app')

@section ('title', 'Asset Issuance Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class=" mb-0">Asset Return Management </h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.assetissuance.partials.assetissuance-header-buttons')
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
                            <table id="assetissuance-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>#</th>
                                        <th>Requisition Number</th>
                                        <th>Employee</th>
                                        <th>Date Issued</th>
                                        <th>Expected Return Date</th>
                                        <th>Notes</th>
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
    {{ Form::open(['route' => ['biller.assetreturned.index'], 'method' => 'GET']) }}
        {{ Form::hidden('id', null, ['id' => 'quote']) }}
    {{ Form::close() }}
</div>
@endsection

@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    // $('.select-row').click(function (e) { 
    //     alert('hello');
        
    // });
    setTimeout(() => draw_data(), "{{ config('master.delay') }}");
    $('#assetissuance-table').on('change', '.select-row', this.selectRow);
    

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

    function selectRow() {
        console.log($(this).val());
            const el = $(this);
            if (el.prop('checked')) {
                $('#quote').val(el.val());
                $('#assetissuance-table tbody tr').each(function() {
                    const row = $(this);
                    if (row.find('.select-row').val() != el.val()) {
                        row.find('.select-row').prop('checked', false);
                    }
                });
            } else {
                $('#quote').val('');
                $('#assetissuance-table tbody tr').each(function() {
                    const row = $(this);
                    row.find('.select-row').prop('checked', false);
                });
            }
            if ($('#quote').val()) {
                swal({
                    title: 'Proceed to Return Stock?',
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                    showCancelButton: true,
                }, () => $('form').submit()); 
            }
        }
    function draw_data() {
        const tableLan = {@lang('datatable.strings')};
        var dataTable = $('#assetissuance-table').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: tableLan,
            ajax: {
                url: '{{ route("biller.assetissuance.get") }}',
                type: 'POST',
                data: { c_type: 0 }
            },
            columns: [
                {data: 'checkbox',  searchable: false,  sortable: false},
                {
                    data: 'DT_Row_Index',
                    name: 'id'
                },
                {
                    data: 'acquisition_number',
                    name: 'acquisition_number'
                },
                {
                    data: 'employee_name',
                    name: 'employee_name'
                },
                {
                    data: 'issue_date',
                    name: 'issue_date'
                },
                {
                    data: 'return_date',
                    name: 'return_date'
                },
                {
                    data: 'note',
                    name: 'note'
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