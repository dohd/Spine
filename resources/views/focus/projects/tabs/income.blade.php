<div class="tab-pane" id="tab_data8" aria-labelledby="tab8" role="tabpanel">
    <div class="card-body">
            @php
                $is_invoiced = isset($project->quotes->first()->invoice_product);
            @endphp
            <h5 class="font-weight-bold">Total Income: {{ $is_invoiced? numberFormat($project->quotes->sum('subtotal')) : '0.00' }}</h5>
            <div class="table-responsive">
                <table class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ trans('invoices.invoice') }} No.</th>                    
                            <th>{{ trans('invoices.invoice_date') }}</th>
                            <th>Quote / PI No.</th>
                            <th>Quote Subject</th>
                            <th>Quote Date</th>
                            <th>{{ trans('general.amount') }}</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        @if ($is_invoiced)
                            @foreach ($project->quotes as $i => $quote)
                                <td>{{ $i+1 }}</td>
                                <td>
                                    <a href="{{ route('biller.invoices.show', $quote->invoice_product->invoice) }}">
                                        {{ gen4tid('Inv-', $quote->invoice_product->invoice->tid) }}
                                    </a>
                                </td>                    
                                <td>{{ dateFormat($quote->invoice_product->invoice->invoicedate) }}</td>
                                <td><a href="{{ route('biller.quotes.show', $quote) }}">{{ gen4tid($quote->bank_id? 'PI-' : 'QT-', $quote->tid) }}</a></td>
                                <td>{{ $quote->notes }}</td>
                                <td>{{ dateFormat($quote->date) }}</td>
                                <td>{{ numberFormat($quote->subtotal) }}</td>                   
                            @endforeach
                        @endif   
                    </tbody>
                </table>
            </div>            
             
    </div>
</div>