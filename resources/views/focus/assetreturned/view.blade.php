@extends('core.layouts.app')

@section('title', 'Asset returned Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Asset Returns Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.assetreturned.partials.assetreturned-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <button class="btn btn-primary click btn-sm" type="button" data-toggle="modal" data-target="#exampleModal">Update Lost</button>
            
              </div>
        </div>
        <div class="card-body">
            <table id="assetTbl" class="table table-xs table-bordered">
                <tbody>
                    @php
                        $record = $assetreturned;                        
                        $details = [
                            'Employee Name' => $record->employee_name,
                            'Requisition Number' => $record->acquisition_number,
                            'Issued Date' => dateFormat($record->issue_date),
                            'Expected Return Date' => dateFormat($record->return_date),
                            'Note' => $record->note, 
                        ];
                        $assetreturneds = \App\Models\assetreturned\Assetreturned::where('id',$record->id)->first();
                
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
                <table class="table table-xs table-bordered" id="myTable">
                    <thead>
                        <tr class="item_header bg-gradient-directional-blue white">
                            <th width="6%" class="text-center">#</th>
                            <th width="38%" class="text-center">Product Name</th>
                            <th width="10%" class="text-center">Issued Qty</th> 
                            <th width="10%" class="text-center">Returned</th> 
                            <th width="10%" class="text-center">Lost</th> 
                            <th width="10%" class="text-center">Broken</th>  
                            <th width="10%" class="text-center">Return Date</th>                                                            
                        </tr>
                    </thead>
                    <tbody class="tb">
                         @isset ($assetreturneds)
                            @php ($i = 0)
                            @foreach ($assetreturneds->item as $item)
                                @if ($item)
                                     
                                       
                                <tr class="tr">
                                    <input type="hidden" name="" id="qtyIssued" value="{{$item->qty_issued}}">
                                    <td class="text-center">{{ $item->id }}</td>
                                    <td class="text-center">{{ $item->name }}</td>
                                    <input type="hidden" name="" value="{{ $item->asset_returned_id }}" id="">
                                    <td class="text-center">{{ $item->qty_issued }}</td>
                                    <td class="text-center">{{ $item->returned_item }}</td>
                                    <td class="text-center">{{ $item->lost_items }}</td>
                                    {{-- <td class="text-center">
                                       
                                        @if ($item->qty_issued > 0) 
                                    <input type="hidden" name="" value="{{ $asset = \App\Models\assetreturned\AssetreturnedItems::where('item_id',$item->item_id)->where('asset_returned_id', $record->id)->first() }}" id="">
                                    <input type="hidden" name="" value="{{ $assetReturns = \App\Models\assetreturned\AssetreturnedItems::where('item_id',$item->item_id)->where('qty_issued', '=', '0')->get() }}" id="">
                                    
                                    
                                        @if ($assetReturns )
                                        <input type="hidden" name="" id="qtyIssued" value="{{$ans = $asset->lost_items - $assetReturns->sum('returned_item')}}">
                                        <input type="hidden" name="" value="{{$total_returned = $asset->returned_item + $assetReturns->sum('returned_item')}}">  
                                        {{ $ans }}
                                        @else
                                        @endif
                                     @endif
                                    </td> --}}
                                    
                                     
                                    
                                    <td class="text-center">{{ $item->broken }}</td> 
                                    <td class="text-center">{{ $item->actual_return_date }}</td>     
                                    <input type="hidden" name="" id="lost" value="{{$item->lost_items}}">
                                    <input type="hidden" name="" id="item_id" value="{{$item->item_id}}">  
                                    <input type="hidden" name="prod_id" id="productId" value="{{ $item->item_id }}">                             
                                </tr>
                                    @php ($i++)
                                @endif
                            @endforeach
                        @endisset
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <table class="table table-xs table-bordered" id="myTable">
                    <thead>
                        <tr class="item_header bg-gradient-directional-blue white">
                            <th class="text-center">Product Name</th>
                            <th class="text-center">Issued Qty</th>
                            <th  class="text-center">Returned Qty</th> 
                            {{-- <th class="text-center">Remaining Items</th> --}}
                            <th class="text-center">Lost</th>
                            <th class="text-center">Total Broken</th>
                            <th class="text-center">Items to Pay</th>                                                       
                        </tr>
                    </thead>
                    <tbody class="tb">
                         @isset ($assetreturneds)
                            @php ($i = 0)
                            @foreach ($assetreturneds->item as $item)
                                @if ($item->qty_issued >0)
                                     
                                       
                                <tr>
                                    <td class="text-center">{{ $item->name }}</td>
                                    <td class="text-center">{{ $item->qty_issued }}</td>
                                    
                                    <td class="text-center">
                                       
                                        @if ($item->qty_issued > 0) 
                                            <input type="hidden" name="" value="{{ $total = \App\Models\assetreturned\AssetreturnedItems::where('item_id',$item->item_id)->where('asset_returned_id', $record->id)->sum('returned_item') }}" id="">
                                        @endif
                                        {{$total}}
                                    </td> 
                                    {{-- <td class="text-center">{{ $item->qty_issued - $total - $item->lost_items -$item->broken  }}</td> --}}
                                    <td class="text-center">{{ $item->lost_items }}</td>
                                    <td class="text-center">
                                       
                                        @if ($item->qty_issued > 0) 
                                            <input type="hidden" name="" value="{{ $broken_items = \App\Models\assetreturned\AssetreturnedItems::where('item_id',$item->item_id)->where('asset_returned_id', $record->id)->sum('broken') }}" id="">
                                        @endif
                                        {{$broken_items}}
                                    </td> 
                                    <td class="text-center">{{ $item->lost_items + $broken_items }}</td>
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
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog mw-100" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Returns Products</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                  <div class="col-5">
                      <label for="payer" class="caption">Search Product</label>                                       
                      <input type="text" class="form-control" name="name" id="productbox" data-placeholder="Search Product" />
                      <input type="hidden" name="employee_id" value="{{ @$purchase->employee_id || 1 }}" id="employeeid">
                  </div> 
                  <div class="card mt-3">
                      <div class="card-body">
                          <table id="table" class="table table-bordered" width="75%">
                              <thead>
                                  <tr class="item_header bg-gradient-directional-blue white">
                                      <th width="6%" class="text-center">#</th>
                                      <th width="38%" class="text-center">Product Name</th>
                                      <th width="10%" class="text-center">Issued Qty</th> 
                                      <th width="10%" class="text-center">Returned</th> 
                                      <th width="10%" class="text-center">Lost</th> 
                                      <th width="10%" class="text-center">Broken</th>
                                      <th width="10%" class="text-center">Date</th>                                                            
                                  </tr>
                              </thead>
                              <tbody class="tbody">
                                   @isset ($assetreturneds)
                                      @php ($i = 0)
                                      @foreach ($assetreturneds->item as $item)
                                          @if ($item->lost_items >0)
                                          <tr class="row1">
                                              <td class="text-center idItem">{{ $item->id }}</td>
                                              <td class="text-center name">{{ $item->name }}</td>
                                              <input type="hidden" name="" value="{{ $item->asset_returned_id }}" id="assetId">
                                              <td class="text-center issued">{{ $item->qty_issued }}</td>
                                              <td class="text-center returned_item">
                                                @if ($item->qty_issued > 0) 
                                                <input type="text" class="form-control" name="return" value=" {{ $total = \App\Models\assetreturned\AssetreturnedItems::where('item_id',$item->item_id)->where('asset_returned_id', $record->id)->sum('returned_item') }}" id="return" disabled>
                                                @endif
                                                </td>
                                                <td class="text-center lost"><input type="text" class="form-control" name="lost" value="{{ $item->lost_items }}" id="lost" disabled></td>
                                              <td class="text-center broken"><input type="text" class="form-control broken" name="broken" value="{{ $item->broken }}" id="broken" disabled></td>
                                              <td class="text-center date"><input type="date" class="form-control" name="date" id="date"></td>                                        
                                              <input type="hidden" name="id" id="item" value="{{ $item->id }}">
                                              <input type="hidden" name="prod_id" id="prod_id" value="{{ $item->item_id }}">
                                              <input type="hidden" id="lost" value="{{ $item->lost_items }}">
                                              <input type="hidden" id="serial_number" value="{{ $item->serial_number }}">
                                              
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
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary submit">Submit</button>
              </div>
            </div>
          </div>
@endsection
@section('extra-scripts')
<script>
    checkLost();
    // $('.row1').click(function () { 
    //     var rows = $('tr', '.tbody');
    //     rows.eq(0).addClass('my_class');
    //     console.log($('#id').val());
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});
        
    // });
    $('#productbox').keyup(function () { 
        var value = $(this).val();
        table_search($('#productbox').val(),$('#table tbody tr'),'012');
    });
    function checkLost() { 
        $('#myTable tbody tr').each(function() {
            var qtyIssued = $(this).find('#qtyIssued').val();   
            //console.log(qtyIssued); 
            if (qtyIssued > 0) {
                var lost = $(this).find('#lost').val();
            }
        });
     }
    $('table tbody tr').one('click',function(){
       // console.log($(this).first().text());
       
        var $tr    = $(this).closest('.row1');
        var $clone = $tr.clone().removeClass('row').attr('id','clone1');
        //.append(`<input type="text" id="itemid" value="{{ $item->id }}">`);;
        $clone.find(':text').val('').prop( "disabled", false );
        $tr.after($clone);
        const lost_item_url = "{{ route('biller.assetreturned.send') }}";

        
        $('.submit').click(function () { 
            $('#date').prop('disabled', false);
            var id = $('#clone1 .idItem').text();
            var product_name = $('#clone1 .name').text();
            var assetId = $('#clone1').find('#assetId').val();
            var lost_item = $('#clone1').find('.lost input').val();
            var broken = $('#clone1').find('.broken input').val();
            var returned_item = $('#clone1').find('.returned_item input').val();
            var lost_date = $('#clone1').find('.date input').val();
            var product_id = $('#clone1').find('#prod_id').val();
            var serial_number = $('#clone1').find('#serial_number').val();
           data = {
                item_lost_id: id,
                item_lost_qty: lost_item,
                returned_item: returned_item,
                assetId: assetId,
                name: product_name,
                lost_date: lost_date,
                product_id: product_id,
                serial_number: serial_number,
                broken: broken,
           }
            // console.log($('#clone1').find('.date input').val());
            
            $.ajax({
            method: "POST",
            url: lost_item_url,
            data: data,
            success: function(response) {
                //console.log(response);
                window.location.reload();
            }
            
        });
            
        });
    }); 

    function table_search(search,tr,indexSearch='0') {
    //check if element don't exist in dom
    var regEx=/^[0-9]*$/;
    if (tr.length==0 || !regEx.test(indexSearch)){
        return;
    }
    /*hide tr don't contain search in input*/
    for (var i = 0; i <tr.length ; i++) {
        var resule='false';
        for (var j = 0; j < indexSearch.length ; j++) {
            if (tr.eq(i).children().length > indexSearch[j]) {
                if (tr.eq(i).children().eq(indexSearch[j]).text().indexOf(search)!=-1){
                    resule='success';
                    break;
                }
            }
        }
        if (resule=='success'){
            tr.eq(i).show();
            
        } else {
            tr.eq(i).hide();
        }
    }
}
</script>
@endsection
