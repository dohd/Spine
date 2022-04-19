@extends ('core.layouts.app')

@section ('title', 'Loans Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Loans Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right mr-3">
                <div class="media-body media-right text-right">
                    @include('focus.loans.partials.loans-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <table id="loansTbl" class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                <tbody>
                    @php
                        $loan_details = [
                            'Loan ID' => $loan->tid,
                            'Date' => dateFormat($loan->date),
                            'Approval Status' => $loan->is_approved ? 'Approved' : 'Pending',
                            'Lender' => $loan->lender->holder,
                            'Bank Account' => $loan->bank->holder,
                            'Loan Period' => $loan->time_pm . ' months',
                            'Loan Amount' => number_format($loan->amount, 2),
                            'Amount Payable (monthly)' => number_format($loan->amount_pm, 2),
                            'Status' => $loan->status, 
                            'Amount Paid' => number_format($loan->amountpaid, 2),
                            'Note' => $loan->note,
                        ];
                    @endphp
                    @foreach ($loan_details as $key => $val)
                        <tr>
                            <th>{{ $key }}</th>
                            <td>{{ $val }}</td>
                        </tr> 
                    @endforeach                                      
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection