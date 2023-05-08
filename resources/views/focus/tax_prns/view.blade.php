@extends ('core.layouts.app')

@section('title', 'Tax PRN Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Tax PRN Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.tax_prns.partials.tax-prn-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        @php
                            $details = [
                                'Return Number' => $tax_prn->code,
                                'Period From' => dateFormat($tax_prn->period_from),
                                'Period To' => dateFormat($tax_prn->period_to),
                                'Payment Mode' => ucfirst($tax_prn->payment_mode),
                                'Amount' => numberFormat($tax_prn->amount),
                                'Acknowledgement Date' => dateFormat($tax_prn->date),
                                'Remark' => $tax_prn->note,
                            ];
                        @endphp
                        @foreach ($details as $key => $val)
                            <tr>
                                <th width="30%">{{ $key }}</th>
                                <td>
                                    @if ($key == 'Return Number')
                                        <span class="mr-1">{{ $val }}</span>
                                        <a class="btn btn-purple btn-sm" href="{{ route('biller.tax_reports.filed_report', ['return_month' => substr(dateFormat($tax_prn->period_from), 3)]) }}" title="Tax Returns">
                                            <i class="fa fa-list"></i> List
                                        </a>  
                                    @else
                                        {{ $val }}
                                    @endif  
                                </td>
                            </tr>                           
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
