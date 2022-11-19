@extends ('core.layouts.app')

@section('title', 'Create | Tax Return Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Tax Return Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.tax_reports.partials.tax-report-header-buttons')
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
                                'Report Title' => $tax_report->title,
                                'Filed Returns' => '',
                                'Sale Filing Month' => $tax_report->sale_month,
                                'Sale Tax Rate' => +$tax_report->sale_tax_rate . '%',
                                'Sale Taxable Amount' => numberFormat($tax_report->sale_subtotal),
                                'Sale Tax' => numberFormat($tax_report->sale_tax),
                                'Sale Total Amount' => numberFormat($tax_report->sale_total),
                                'Purchase Filing Month' => $tax_report->purchase_month,
                                'Purchase Tax Rate' => +$tax_report->purchase_tax_rate . '%',
                                'Purchase Taxable Amount' => numberFormat($tax_report->purchase_subtotal),
                                'Purchase Tax' => numberFormat($tax_report->purchase_tax),
                                'Purchase Total Amount' => numberFormat($tax_report->purchase_total),
                            ];
                        @endphp
                        @foreach ($details as $key => $val)
                            <tr>
                                <th width="30%">{{ $key }}</th>
                                <td>
                                    @if ($key == 'Filed Returns')
                                        <a class="btn btn-purple btn-sm" href="{{ route('biller.tax_reports.filed_report', ['tax_report_id' => $tax_report->id]) }}" title="tax returns">
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
