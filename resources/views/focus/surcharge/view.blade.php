@extends('core.layouts.app')

@section('title', 'Surcharges Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Surcharges Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    {{-- @include('focus.surcharge.partials.assetissuance-header-buttons') --}}
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
                        $record = $surcharge;                        
                        $details = [
                            'Employee Name' => $record->employee_name,
                            'Category Issue' => $record->issue_type,
                            'From Date' => dateFormat($record->date),
                            'Number of Month' => $record->months,
                            'Cost' => $record->cost, 
                        ];
                        $surcharges = \App\Models\surcharge\Surcharge::where('id',$record->id)->first();
                
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
                            <th width="38%" class="text-center">Date Per Month</th>
                            <th width="10%" class="text-center">Cost Per month</th> 
                            <th width="10%" class="text-center">Status</th>                                                            
                        </tr>
                    </thead>
                    <tbody>
                         @isset ($surcharges)
                            @php ($i = 0)
                            @foreach ($surcharges->item as $item)
                                @if ($item)
                                <tr>
                                    <td class="text-center">{{ $item->id }}</td>
                                    <td class="text-center">{{ $item->datepermonth }}</td>
                                    <td class="text-center">{{ $item->costpermonth }}</td>
                                    <td class="text-center">{{ $item->status == '1' ? 'Paid' : 'Pending' }}</td>
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
