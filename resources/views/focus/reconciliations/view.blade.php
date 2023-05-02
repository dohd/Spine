@extends ('core.layouts.app')

@section ('title', 'Reconciliations Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Reconciliations Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.reconciliations.partials.reconciliations-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <table id="journalsTbl" class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                <tbody>
                    @php
                        $reconciliation_details = [
                            'Account' => $reconciliation->account->holder,
                            'Date' => dateFormat($reconciliation->start_date) . ' : ' . dateFormat($reconciliation->end_date),
                            'System Balance' => number_format($reconciliation->system_amount),
                            'Opening Balance' => number_format($reconciliation->open_amount, 2),
                            'Closing Balance' => number_format($reconciliation->close_amount, 2),                          
                        ];
                    @endphp
                    @foreach ($reconciliation_details as $key => $val)
                        <tr>
                            <th>{{ $key }}</th>
                            <td>{{ $val }}</td>
                        </tr> 
                    @endforeach                                      
                </tbody>
            </table>

            <div class="table-responsive">        
                <table id="ledgerTbl" class="table">
                    <thead>
                        <tr class="bg-gradient-directional-blue white">
                            <th class="text-center">Date</th>
                            <th>Transaction ID</th>
                            <th width="40%" class="text-center">Note</th>
                            <th width="12%" class="text-center">Debit</th>
                            <th width="12%" class="text-center">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reconciliation->items as $item)
                            <tr>
                                <td>{{ dateFormat($item->tr_date) }}</td>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->note }}</td>
                                <td>{{ number_format($item->debit, 2) }}</td>
                                <td>{{ number_format($item->credit, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection