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
            <button class="btn btn-primary btn-sm" type="button" data-toggle="modal" data-target="#exampleModal">Update Lost</button>
            
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
                <table class="table table-xs table-bordered">
                    <thead>
                        <tr class="item_header bg-gradient-directional-blue white">
                            <th width="6%" class="text-center">#</th>
                            <th width="38%" class="text-center">Product Name</th>
                            <th width="10%" class="text-center">Issued Qty</th> 
                            <th width="10%" class="text-center">Returned</th> 
                            <th width="10%" class="text-center">Lost</th> 
                            <th width="10%" class="text-center">Broken</th>                                                            
                        </tr>
                    </thead>
                    <tbody>
                         @isset ($assetreturneds)
                            @php ($i = 0)
                            @foreach ($assetreturneds->item as $item)
                                @if ($item)
                                <tr>
                                    <td class="text-center">{{ $item->id }}</td>
                                    <td class="text-center">{{ $item->name }}</td>
                                    <td class="text-center">{{ $item->qty_issued }}</td>
                                    <td class="text-center">{{ $item->returned_item }}</td>
                                    <td class="text-center">{{ $item->lost_items }}</td>
                                    <td class="text-center">{{ $item->broken }}</td>
                                    
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
                            </tr>
                        </thead>
                        <tbody class="tbody">
                             @isset ($assetreturneds)
                                @php ($i = 0)
                                @foreach ($assetreturneds->item as $item)
                                    @if ($item)
                                    <tr class="row1">
                                        <td class="text-center"><input type="text" class="form-control" name="" value="{{ $item->id }}" id="id" disabled></td>
                                        <td class="text-center">{{ $item->name }}</td>
                                        <td class="text-center">{{ $item->qty_issued }}</td>
                                        <td class="text-center">{{ $item->returned_item }}</td>
                                        <td class="text-center">{{ $item->lost_items }}</td>
                                        <td class="text-center">{{ $item->broken }}</td>
                                        
                                        
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
          <button type="button" class="btn btn-primary">Submit</button>
        </div>
      </div>
    </div>
@endsection
@section('extra-scripts')
<script>
    // $('.row1').click(function () { 
    //     var rows = $('tr', '.tbody');
    //     rows.eq(0).addClass('my_class');
    //     console.log($('#id').val());
        
    // });
    // $('#productbox').keyup(function () { 
    //     var value = $(this).val();
    //     table_search($('#productbox').val(),$('#table tbody tr'),'012');
    // });
    $('.ids').click(function () { 
        console.log($('.ids').val());
        
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
            console.log($('#id').val());
           if ($('#productbox').val()) {
            $('.tbody').append(`<tr>
                                        <td class="text-center"><input type="text" class="form-control" name="" value="{{ $item->id }}" id="id" disabled></td>
                                        <td class="text-center"><input type="text" name="" class="form-control" value="{{ $item->name }}" id="name" disabled></td>
                                        <td class="text-center"><input type="text" name="" class="form-control" value="0" id="qty_issued" disabled></td>
                                        <td class="text-center"><input type="text" name=""class="form-control" value="" id="lost"></td>
                                        <td class="text-center"><input type="text" name="" class="form-control" value="" id="lost"></td>
                                        <td class="text-center"><input type="text" name="" class="form-control" value="" id="broken"></td>
                                        
                                    </tr>`);
           }
           else{
            $('.tbody tr').each(function () {
                if (!$.trim($(this).text())) $(this).remove();
            });
           }
        } else {
            tr.eq(i).hide();
        }
    }
}
</script>
@endsection
