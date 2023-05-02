<div class="tab-pane" id="tab_data9" aria-labelledby="tab9" role="tabpanel">
    <div class="card-body">
        <h5 class="font-weight-bold">Total Expense Amount: {{ numberFormat($project->purchase_items->sum('amount')) }}</h5>
        <br>
        <h5>Total Bill Item Amount: {{ numberFormat($project->purchase_items->sum('amount')) }}</h5>
        <div class="table-responsive">
            <table class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Bill</th>
                        <th>Type</th>
                        <th>Description</th>                    
                        <th>UoM</th>
                        <th>Qty</th>
                        <th>Amount</th>                 
                    </tr>
                </thead>
                <tbody>
                    @foreach ($project->purchase_items as $i => $item)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td><a href="{{ route('biller.purchases.edit', $item->purchase) }}">{{ gen4tid('DP-', $item->purchase->tid) }}</a></td>
                            <td>{{ $item->type }}</td>
                            <td>{{ $item->description }}</td>
                            <td>{{ $item->uom }}</td>
                            <td>{{ +$item->qty }}</td>
                            <td>{{ numberFormat($item->amount) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>  
        <br>
        <h5>Total Issued Stock Item Amount: <span>{{ numberFormat(0) }}</span></h5>
        <div class="table-responsive">
            <table class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Quote / PI No.</th>
                        <th>Description</th>                    
                        <th>UoM</th>
                        <th>Qty</th>
                        <th>Warehouse</th>
                        <th>Amount</th>                 
                    </tr>
                </thead>
                <tbody>
                    @foreach ($project->quotes as $quote)
                        @foreach ($quote->projectstock as $projectstock)
                            @foreach ($projectstock->items as $i => $item)
                                <tr>
                                    <td>{{ $i+1 }}</td>
                                    <td>{{ gen4tid($quote->bank_id? 'PI-' : 'QT-', $quote->tid) }}</td>
                                    <td>{{ $item->product->name }}</td>
                                    <td>{{ $item->unit }}</td>
                                    <td>{{ +$item->qty }}</td>
                                    <td>{{ $item->product->warehouse->title }}</td>
                                    <td>{{ numberFormat($item->amount) }}</td>
                                </tr>                                
                            @endforeach                            
                        @endforeach                        
                    @endforeach
                </tbody>
            </table>
        </div>  
    </div>
</div>