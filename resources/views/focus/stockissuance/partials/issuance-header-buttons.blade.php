@php
    $valid_token = token_validator('', 'q'.$quote->id .$quote->tid, true);
    $budget_url = route('biller.print_budget', [$quote->id, 4, $valid_token, 1]);
    $quote_url = route('biller.print_budget_quote', [$quote->id, 4, $valid_token, 1]);
@endphp
<div class="btn-group">
    <a href="{{ $budget_url }}" class="btn btn-purple" target="_blank">
        <i class="fa fa-print"></i> Store
    </a>&nbsp;
    <a href="{{ $quote_url }}" class="btn btn-secondary" target="_blank">
        <i class="fa fa-print"></i> Technician
    </a>
    <a href="{{ route('biller.stockissuance.index') }}" class="btn btn-primary ml-1">
        <i class="ft-list"></i> Quote
    </a>
</div>