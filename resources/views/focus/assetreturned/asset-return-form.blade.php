
<div class="form-group row">
    <div class="col-12">
        <h3 class="title">Return Asset</h3>  
    </div>
</div>
@php
   // $assetreturned = \App\Models\assetreturned\Assetreturned::where('id',$assetreturned)->first();
    //dd($assetreturned->item);
@endphp

<div class="row">
    <div class="col-12 cmp-pnl">
        <div id="employee_namepanel" class="inner-cmp-pnl">                        
            <div class="form-group row"> 
                <div class="col-5">
                    <label for="employee_name" class="caption">Employee</label>
                    <input type="hidden" name="employee_name" value="{{$assetreturned->employee_name}}">                                       
                    {{ Form::text('employee_name', $assetreturned->employee_name, ['class' => 'form-control', 'name'=>'employee_name', 'readonly']) }}
                </div> 
                <div class="col-3">
                    <label for="issue_date" class="caption">Issue Date</label>     
                    <input type="hidden" name="issue_date" value="{{$assetreturned->issue_date}}">                                   
                    {{ Form::text('issue_date', $assetreturned->issue_date, ['class' => 'form-control', 'readonly']) }}
                </div> 
                <div class="col-2">
                    <label >Expected Return Date</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                        <input type="hidden" name="return_date" value="{{$assetreturned->return_date}}"> 
                        {{ Form::text('date', $assetreturned->return_date, ['class' => 'form-control round datepicker', 'id' => 'date', 'readonly']) }}
                    </div>
                </div> 
                <div class="col-2">
                    <label for="toAddInfo" class="caption">Requisition Number *</label>
                    <input type="hidden" name="acquisition_number" value="{{$assetreturned->acquisition_number}}">
                        {{ Form::text('acquisition_number', $assetreturned->acquisition_number, ['class' => 'form-control', 'id'=>'requisition', 'placeholder'=>'Requisition Number', 'rows'=>'1']) }}
                    </div>
                </div>                                                               
            </div> 
        </div>
    </div>
</div>        

<div class="form-group row ml-1">
    <div class="col-10">
        <label for="subject" class="caption">Notes</label>
        <input type="hidden" name="note" value="{{$assetreturned->note}}">
        {{ Form::text('notes', $assetreturned->note, ['class' => 'form-control','name'=>'note', 'id'=>'subject', 'readonly']) }}
    </div>
     
</div>

<div class="form-group center">
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
            </tr>
        </thead>
        <tbody>
             @isset ($assetreturned)
                @php ($i = 0)
                @foreach ($assetreturned->item as $item)
                    @if ($item)
                    <tr>
                        <td class="text-center">{{ $item->id }}</td>
                        <td class="text-center">{{ $item->name }}</td>
                        <td class="text-center">{{ $item->qty_issued }}</td>
                        <td class="text-center"><input class="form-control" type="number" name="returned_item[]" value="{{ $item->returned_item }}"></td>
                        <td class="text-center"><input class="form-control" type="number" name="lost_items[]" value="{{ $item->lost_items }}"></td>
                        <td class="text-center"><input class="form-control" type="number" name="broken[]" value="{{ $item->broken }}"></td>
                        <td class="text-center"><input class="form-control datepicker" id="actual_return_date" type="date" name="actual_return_date[]" value="{{ $item->actual_return_date }}"></td>
                        <input type="hidden" name="item_id[]" value="{{ $item->item_id }}">
                        <input type="hidden" name="qty_issued[]" value="{{ $item->qty_issued }}">
                        <input type="hidden" name="purchase_price[]" value="{{ $item->purchase_price }}">
                        <input type="hidden" name="name[]" value="{{ $item->name }}">
                        <input type="hidden" name="id[]" value="{{ $item->id }}">
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
        {{ Form::submit(@$assetreturned? 'Update' : 'Update', ['class' =>'btn btn-primary btn-lg']) }}
    </div>
</div>                                                  
</div>
@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script>
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    $('#actual_return_date').datepicker('setDate', new Date());

</script>
@endsection

