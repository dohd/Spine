
<div class="form-group row">
    <div class="col-12">
        <h3 class="title">Return Asset</h3>  
    </div>
</div>
@php
    $assetissuances = \App\Models\assetissuance\Assetissuance::where('id',$assetissuance)->first();
    //dd($assetissuances->item);
@endphp

<div class="row">
    <div class="col-12 cmp-pnl">
        <div id="employee_namepanel" class="inner-cmp-pnl">                        
            <div class="form-group row"> 
                <div class="col-5">
                    <label for="employee_name" class="caption">Employee</label>
                    <input type="hidden" name="employee_name" value="{{$assetissuances->employee_name}}">                                       
                    {{ Form::text('employee_name', $assetissuances->employee_name, ['class' => 'form-control', 'name'=>'employee_name', 'readonly']) }}
                </div> 
                <div class="col-3">
                    <label for="issue_date" class="caption">Issue Date</label>     
                    <input type="hidden" name="issue_date" value="{{$assetissuances->issue_date}}">                                   
                    {{ Form::text('issue_date', $assetissuances->issue_date, ['class' => 'form-control', 'readonly']) }}
                </div> 
                <div class="col-2">
                    <label >Expected Return Date</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                        <input type="hidden" name="return_date" value="{{$assetissuances->return_date}}"> 
                        {{ Form::text('date', $assetissuances->return_date, ['class' => 'form-control round datepicker', 'id' => 'date', 'readonly']) }}
                    </div>
                </div> 
                <div class="col-2">
                    <label for="toAddInfo" class="caption">Requisition Number*</label>
                    <input type="hidden" name="acquisition_number" value="{{$assetissuances->acquisition_number}}">
                        {{ Form::text('acquisition_number', $assetissuances->acquisition_number, ['class' => 'form-control', 'id'=>'requisition', 'placeholder'=>'Requisition Number', 'rows'=>'1', '']) }}
                    </div>
                </div>                                                               
            </div> 
        </div>
    </div>
</div>        

<div class="form-group row">
    <div class="col-10">
        <label for="subject" class="caption">Notes</label>
        <input type="hidden" name="note" value="{{$assetissuances->note}}">
        {{ Form::text('notes', $assetissuances->note, ['class' => 'form-control','name'=>'note', 'id'=>'subject', 'readonly']) }}
    </div>
     
</div>

<div class="form-group">
    <table id="productsTbl" class="table-responsive tfr my_stripe_single" style="min-height: 150px;">
        <thead>
            <tr class="item_header bg-gradient-directional-blue white">
                <th width="6%" class="text-center">#</th>
                <th width="38%" class="text-center">Product Name</th>
                <th width="8%" class="text-center">Issued Qty</th>                                
                <th width="15%" class="text-center">Return Qty</th>
                <th width="8%" class="text-center">Lost</th>     
                <th width="12%" class="text-center">Broken</th>
                <th width="12%" class="text-center">Return Date</th>
                <th width="7%" class="text-center">Action</th>                             
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
                        <td class="text-center"><input class="form-control" type="number" name="returned_item[]"></td>
                        <td class="text-center"><input class="form-control" type="number" name="lost_items[]"></td>
                        <td class="text-center"><input class="form-control" type="number" name="broken[]"></td>
                        <td class="text-center"><input class="form-control" type="date" name="actual_return_date[]"></td>
                        <input type="hidden" name="item_id[]" value="{{ $item->item_id }}">
                        <input type="hidden" name="qty_issued[]" value="{{ $item->qty_issued }}">
                        <input type="hidden" name="purchase_price[]" value="{{ $item->purchase_price }}">
                        <input type="hidden" name="name[]" value="{{ $item->name }}">
                        <input type="hidden" name="serial_number[]" value="{{ $item->serial_number }}">
                        
                    </tr>
                        @php ($i++)
                    @endif
                @endforeach
            @endisset
        </tbody>
    </table>
</div>   
<div class="row mt-1">                            
    <div class="col-2 ml-auto">  
        {{ Form::submit(@$billpayment? 'Create' : 'Create', ['class' =>'btn btn-primary btn-lg']) }}
    </div>
</div>                                                  
</div>
