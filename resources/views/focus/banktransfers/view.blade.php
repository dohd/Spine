@extends ('core.layouts.app')
@section ('title', 'Transfers management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Transfers Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.banktransfers.partials.banktransfers-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            @php
                                $note_arr = explode('-', $banktransfer->note);
                                if (@$note_arr[0]) $tr_method = trim($note_arr[0]);
                                if (@$note_arr[1]) {
                                    $note_arr2 =  explode(' ', trim($note_arr[1]));
                                    if (@$note_arr2[0]) $tr_ref = $note_arr2[0];
                                    if (@$note_arr2 > 1) $tr_note = implode(' ', array_slice($note_arr2, 1));
                                }

                                $details = [
                                    'Transaction No.' => $banktransfer->tid,
                                    'Transfer From Bank' => @$banktransfer->trans_from->account->holder,
                                    'Transfer To Bank' => @$banktransfer->account->holder,
                                    'Date' => dateFormat($banktransfer->tr_date),
                                    'Transaction Method' => @$tr_method,
                                    'Reference No.' => @$tr_ref,
                                    'Amount' => numberFormat($banktransfer->debit),
                                    'Note' => @$tr_note,
                                ];
                            @endphp
                            @foreach($details as $key => $value)
                                <div class="row">
                                    <div class="col-3 border-blue-grey border-lighten-5" style="padding: .8em">
                                        <p>{{ $key }}</p>
                                    </div>
                                    <div class="col border-blue-grey border-lighten-5 font-weight-bold" style="padding: .8em">
                                        <p>{{ $value }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
