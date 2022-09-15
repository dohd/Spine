<div class="tab-pane" id="tab_data10" aria-labelledby="tab10" role="tabpanel">
    <div class="card-body">
        <h5>1. Quotation / Proforma Invoice</h5>
        <div class="table-responsive">
            <table class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Quote / PI</th>
                        <th>Actual Amount</th>
                        <th>Estimated Amount</th>                    
                        <th>Profit (Estimate - Actual)</th>
                        <th>% Profit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($project->quotes as $quote)
                        @php
                            $estimated_amount = $quote->subtotal;
                            $actual_amount = 0;
                            foreach ($quote->products as $item) {
                                $actual_amount += $item->estimate_qty * $item->buy_price;
                            }
                            $balance = $estimated_amount - $actual_amount;
                        @endphp
                        <tr>
                            <td>{{ gen4tid($quote->bank_id? 'PI-' : 'QT-', $quote->tid) }}</td>
                            <td>{{ numberFormat($actual_amount) }}</td>
                            <td>{{ numberFormat($estimated_amount) }}</td>
                            <td>{{ numberFormat($balance) }}</td>
                            <td>{{ round($balance / $estimated_amount * 100) }} %</td>
                        </tr>
                    @endforeach
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
                        <th>Projected Sale</th>                    
                        <th>Estimated Expense</th>
                        <th>Profit (Sale - Expense)</th>
                        <th>% Profit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($project->quotes as $quote)
                        @php
                            $actual_amount = $quote->subtotal;
                            $estimated_amount = 0;
                            if ($quote->budget) $estimated_amount = $quote->budget->budget_total;
                            $balance = $actual_amount - $estimated_amount;
                        @endphp
                        <tr>
                            <td>{{ gen4tid($quote->bank_id? 'PI-' : 'QT-', $quote->tid) }}</td>
                            <td>{{ numberFormat($actual_amount) }}</td>
                            <td>{{ numberFormat($estimated_amount) }}</td>
                            <td>{{ numberFormat($balance) }}</td>
                            <td>{{ round($balance / $estimated_amount * 100) }} %</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>   
        {{-- direct purchase expense --}}
        <h5>3. Expense</h5>
        <div class="table-responsive">
            <table class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Quote / PI (Budget)</th>
                        <th>Projected Sale</th>                    
                        <th>Actual Expense</th>
                        <th>Profit (Sale - Expense)</th>
                        <th>% Profit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($project->quotes as $quote)
                        @php
                            $actual_amount = $quote->subtotal;
                            $expense_amount = $project->purchase_items->sum('amount');
                            $expense_amount = 1 / $project->quotes->count() * $expense_amount;  
                            $balance = $actual_amount - $expense_amount;
                        @endphp
                        <tr>
                            <td>{{ gen4tid($quote->bank_id? 'PI-' : 'QT-', $quote->tid) }}</td>
                            <td>{{ numberFormat($actual_amount) }}</td>
                            <td>{{ numberFormat($expense_amount) }}</td>
                            <td>{{ numberFormat($balance) }}</td>
                            <td>{{ round($balance / $expense_amount * 100) }} %</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>   
        {{-- verification --}}
        <h5>4. Verification</h5>
        <div class="table-responsive">
            <table class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Quote / PI (Budget)</th>
                        <th>Verified Sale</th>                    
                        <th>Actual Expense</th>
                        <th>Profit (Sale - Expense)</th>
                        <th>% Profit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($project->quotes as $quote)
                        @php
                            $actual_amount = $quote->verified_amount;
                            $expense_amount = $project->purchase_items->sum('amount');
                            $expense_amount = 1 / $project->quotes->count() * $expense_amount;  
                            $balance = $actual_amount - $expense_amount;
                        @endphp
                        <tr>
                            <td>{{ gen4tid($quote->bank_id? 'PI-' : 'QT-', $quote->tid) }}</td>
                            <td>{{ numberFormat($actual_amount) }}</td>
                            <td>{{ numberFormat($expense_amount) }}</td>
                            <td>{{ numberFormat($balance) }}</td>
                            <td>{{ round($balance / $expense_amount * 100) }} %</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>   
    </div>
</div>
