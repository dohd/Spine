@extends('core.layouts.app')

@section('title', 'Asset Issuance Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Asset Issuance Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.assetissuance.partials.assetissuance-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header"></div>
        <div class="card-body">
            <table id="assetTbl" class="table table-xs table-bordered">
                <tbody>
                    @php
                        $record = $assetissuance;                        
                        $details = [
                            'Employee Name' => $record->employee_name,
                            'Acquisition Number' => $record->acquisition_number,
                            'Issued Date' => dateFormat($record->issue_date),
                            'Expected Return Date' => dateFormat($record->return_date),
                            'Note' => $record->note, 
                        ];
                        $assetissuances = \App\Models\assetissuance\Assetissuance::where('id',$record->id)->first();
                
                    @endphp
                    @foreach ($details as $key => $val)
                        <tr>
                            <th width="50%">{{ $key }}</th>
                            <td>{{ $val }}</td>
                        </tr> 
                    @endforeach     
                                                 
                </tbody>
            </table>
        </div>
        <div class="card">
            <div class="card-body">
                <table class="table table-xs table-bordered">
                    <thead>
                        <tr class="item_header bg-gradient-directional-blue white">
                            <th width="6%" class="text-center">#</th>
                            <th width="38%" class="text-center">Product Name</th>
                            <th width="10%" class="text-center">Issued Qty</th> 
                            <th width="10%" class="text-center">Serial Number</th>                                                            
                        </tr>
                    </thead>
                    <tbody>
                         @isset ($assetissuances)
                            @php ($i = 0)
                            @foreach ($assetissuances->item as $item)
                                @if ($item)
                                <tr>
                                    <td class="text-center">{{ $item->id }}</td>
                                    <td class="text-center">{{ $item->name }}</td>
                                    <td class="text-center">{{ $item->qty_issued }}</td>
                                    <td class="text-center">{{ $item->serial_number }}</td>
                                </tr>
                                    @php ($i++)
                                @endif
                            @endforeach
                        @endisset
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
