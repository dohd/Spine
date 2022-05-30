@extends ('core.layouts.app')

@section ('title',  'Loans Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Loans Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.loans.partials.loans-header-buttons')
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
                            <table id="loansTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Lender</th>
                                        <th>Lending Date</th>
                                        <th>Amount</th>
                                        <th>Lending period (months)</th>
                                        <th>Approval</th>
                                        <th>Amount Paid</th>   
                                        <th>Actions</th>
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
                            <form id="approveLoan"></form>
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

    // Approve Loan
    $('#loansTbl').on('click', '.approve', function() {
        $('#approveForm').attr('action', $(this).attr('data-url'));
        swal({
            title: 'Are You  Sure?',
            icon: "warning",
            buttons: true,
            dangerMode: true,
            showCancelButton: true,
        }, () => $('#approveLoan').submit());
    });

    const language = {@lang('datatable.strings') };
    const dataTable = $('#loansTbl').dataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        language,
        ajax: {
            url: '{{ route("biller.loans.get") }}',
            type: 'post'
        },
        columns: [{
                data: 'DT_Row_Index',
                name: 'id'
            },
            {
                data: 'lender',
                name: 'lender'
            },
            {
                data: 'date',
                name: 'date'
            },
            {
                data: 'amount',
                name: 'amount'
            },
            {
                data: 'time_pm',
                name: 'time_pm'
            },
            {
                data: 'is_approved',
                name: 'is_approved'
            },
            {
                data: 'amountpaid',
                name: 'amountpaid'
            },
            {
                data: 'actions',
                name: 'actions',
                searchable: false,
                sortable: false
            },            
        ],
        order: [
            [0, "desc"]
        ],
        searchDelay: 500,
        dom: 'Blfrtip',
        buttons: ['csv', 'excel', 'print'],
    });
</script>
@endsection