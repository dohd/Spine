<div class="tab-pane" id="tab_data12" aria-labelledby="tab12" role="tabpanel">
    <div class="card-body">
        <h5>1. Quotation / Proforma Invoice</h5>
        <div class="table-responsive">
            <table class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Quote / PI</th>
                        <th>Quoted Amount</th>                    
                        <th>Est. Cost Amount</th>
                        <th>Gross Profit (Quoted - Cost)</th>
                        <th>% Gross Profit</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // aggregate
                        $total_actual = 0;
                        $total_estimate = 0;
                        $total_balance = 0;
                    @endphp
                    @foreach ($project->quotes as $quote)
                        @php
                            $estimated_amount = $quote->subtotal;
                            $actual_amount = 0;
                            foreach ($quote->products as $item) {
                                $actual_amount += $item->estimate_qty * $item->buy_price;
                            }
                            $balance = $estimated_amount - $actual_amount;
                            // aggregate
                            $total_estimate += $estimated_amount;
                            $total_actual += $actual_amount;
                            $total_balance += $balance;
                        @endphp
                        <tr>
                            <td>{{ gen4tid($quote->bank_id? 'PI-' : 'QT-', $quote->tid) }}</td>
                            <td>{{ numberFormat($estimated_amount) }}</td>
                            <td>{{ numberFormat($actual_amount) }}</td>
                            <td>{{ numberFormat($balance) }}</td>
                            <td>{{ round(div_num($balance, $actual_amount) * 100) }} %</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td><b>Totals</b></td>
                        <td>{{ numberFormat($total_estimate) }}</td>
                        <td>{{ numberFormat($total_actual) }}</td>
                        <td>{{ numberFormat($total_balance) }}</td>
                        <td>{{ round(div_num($total_balance, $total_actual) * 100) }} %</td>
                    </tr>
                </tbody>
            </table>
        </div>    

        {{-- budgeting --}}
        <h5>2. Budgeting</h5>
        <div class="table-responsive">
            <table class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Quote / PI (Budget)</th>
                        <th>Quoted Amount</th>                    
                        <th>Est. Cost Amount</th>
                        <th>Gross Profit (Quoted - Cost)</th>
                        <th>% Gross Profit</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // aggregate
                        $total_actual = 0;
                        $total_estimate = 0;
                        $total_balance = 0;
                    @endphp
                    @foreach ($project->quotes as $quote)
                        @php
                            $actual_amount = $quote->subtotal;
                            $estimated_amount = 0;
                            if ($quote->budget) $estimated_amount = $quote->budget->items()->sum(DB::raw('round(new_qty*price)'));
                            $balance = $actual_amount - $estimated_amount;
                            // aggregate
                            $total_actual += $actual_amount;
                            $total_estimate += $estimated_amount;
                            $total_balance += $balance;
                        @endphp
                        <tr>
                            <td>{{ gen4tid($quote->bank_id? 'PI-' : 'QT-', $quote->tid) }}</td>
                            <td>{{ numberFormat($actual_amount) }}</td>
                            <td>{{ numberFormat($estimated_amount) }}</td>
                            <td>{{ numberFormat($balance) }}</td>
                            <td>{{ round(div_num($balance, $estimated_amount) * 100) }} %</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td><b>Totals</b></td>
                        <td>{{ numberFormat($total_actual) }}</td>
                        <td>{{ numberFormat($total_estimate) }}</td>
                        <td>{{ numberFormat($total_balance) }}</td>
                        <td>{{ round(div_num($total_balance, $total_estimate) * 100) }} %</td>
                    </tr>
                </tbody>
            </table>
        </div>   

        {{-- direct purchase expense --}}
        <h5>3. Job Expense</h5>
        <div class="table-responsive">
            <table class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Quote / PI (Budget)</th>
                        <th>Quoted Amount</th>                    
                        <th>Actual Cost</th>
                        <th>Gross Profit (Quoted - Cost)</th>
                        <th>% Gross Profit</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // aggregate
                        $total_actual = 0;
                        $total_estimate = 0;
                        $total_balance = 0;
                    @endphp
                    @foreach ($project->quotes as $quote)
                        @php
                            $actual_amount = $quote->subtotal;
                            $expense_amount = $project->purchase_items->sum('amount') / $project->quotes->count();
                            if ($quote->projectstock) $expense_amount += $quote->projectstock->sum('total');
                            $balance = $actual_amount - $expense_amount;
                            // aggregate
                            $total_actual += $actual_amount;
                            $total_estimate += $expense_amount;
                            $total_balance += $balance;
                        @endphp
                        <tr>
                            <td>{{ gen4tid($quote->bank_id? 'PI-' : 'QT-', $quote->tid) }}</td>
                            <td>{{ numberFormat($actual_amount) }}</td>
                            <td>{{ numberFormat($expense_amount) }}</td>
                            <td>{{ numberFormat($balance) }}</td>
                            <td>{{ round(div_num($balance, $expense_amount) * 100) }} %</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td><b>Totals</b></td>
                        <td>{{ numberFormat($total_actual) }}</td>
                        <td>{{ numberFormat($total_estimate) }}</td>
                        <td>{{ numberFormat($total_balance) }}</td>
                        <td>{{ round(div_num($total_balance, $total_estimate) * 100) }} %</td>
                    </tr>
                </tbody>
            </table>
        </div>   
        
        {{-- verification --}}
        <h5>4. Job Verification</h5>
        <div class="table-responsive">
            <table class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Quote / PI (Budget)</th>
                        <th>Verified Amount</th>                    
                        <th>Actual Cost</th>
                        <th>Profit (Verified - Cost)</th>
                        <th>% Gross Profit</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // aggregate
                        $total_actual = 0;
                        $total_estimate = 0;
                        $total_balance = 0;
                    @endphp
                    @foreach ($project->quotes as $quote)
                        @php
                            $actual_amount = $quote->verified_amount;
                            $expense_amount = $project->purchase_items->sum('amount') / $project->quotes->count();
                            if ($quote->projectstock) $expense_amount += $quote->projectstock->sum('total');
                            $balance = $actual_amount - $expense_amount;
                            // aggregate
                            $total_actual += $actual_amount;
                            $total_estimate += $expense_amount;
                            $total_balance += $balance;
                        @endphp
                        <tr>
                            <td>{{ gen4tid($quote->bank_id? 'PI-' : 'QT-', $quote->tid) }}</td>
                            <td>{{ numberFormat($actual_amount) }}</td>
                            <td>{{ numberFormat($expense_amount) }}</td>
                            <td>{{ numberFormat($balance) }}</td>
                            <td>{{ round(div_num($balance, $expense_amount) * 100) }} %</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td><b>Totals</b></td>
                        <td>{{ numberFormat($total_actual) }}</td>
                        <td>{{ numberFormat($total_estimate) }}</td>
                        <td>{{ numberFormat($total_balance) }}</td>
                        <td>{{ round(div_num($total_balance, $total_estimate) * 100) }} %</td>
                    </tr>
                </tbody>
            </table>
        </div>   
    </div>
</div>
