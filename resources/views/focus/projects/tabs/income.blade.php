<div class="tab-pane" id="tab_data8" aria-labelledby="tab8" role="tabpanel">
    <div class="card-body">
        @isset($project->quotes->first()->invoice_product)
            <h5 class="font-weight-bold">Total Income: {{ numberFormat($project->quotes->sum('subtotal')) }}</h5>
            <div class="table-responsive">
                <table class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ trans('invoices.invoice') }} No.</th>                    
                            <th>{{ trans('invoices.invoice_date') }}</th>
                            <th>Quote Subject</th>
                            <th>Quote Date</th>
                            <th>{{ trans('general.amount') }}</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($project->quotes as $i => $quote)
                            <td>{{ $i+1 }}</td>
                            <td>
                                <a href="{{ route('biller.invoices.show', $quote->invoice_product->invoice) }}">
                                    {{ gen4tid('Inv-', $quote->invoice_product->invoice->tid) }}
                                </a>
                            </td>                    
                            <td>{{ dateFormat($quote->invoice_product->invoice->invoicedate) }}</td>
                            <td>{{ $quote->notes }}</td>
                            <td>{{ dateFormat($quote->date) }}</td>
                            <td>{{ numberFormat($quote->subtotal) }}</td>                   
                        @endforeach
                    </tbody>
                </table>
            </div>            
        @endisset        
    </div>
</div>