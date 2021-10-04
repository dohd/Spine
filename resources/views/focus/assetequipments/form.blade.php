

<div class="row">

    <div class="col-md-4">
                <div class='form-group'>
                    {{ Form::label( 'purchase_date', 'Purchase Date',['class' => 'col control-label']) }}
                    <div class='col'>
                        {{ Form::text('purchase_date', dateFormat(@$assetequipments->purchase_date), ['class' => 'form-control box-size', 'placeholder' => 'Purchase Date','data-toggle'=>'datepicker','autocomplete'=>'false']) }}
                    </div>
                </div>
            </div>


      <div class="col-md-4">
        <div class='form-group'>
            {{ Form::label( 'account_type', 'Account Type',['class' => 'col control-label']) }}
            <div class='col'>
                <select class="form-control required" name="account_type" id="account_type">

                     @if(@$assetequipments->account_type==="")
                        <option value="" selected>--Select Account Type--</option>
                         @endif

                    @if(@$assetequipments->account_type)
                        <option value="{{$assetequipments->account_type}}" selected>{{$assetequipments->account_type}}</option>
                    @endif

                  
                    <option value="Assets">Assets</option>
                    <option value="Expenses">Expenses</option>
                   
                </select>
            </div>
        </div>
    </div>

      <div class="col-md-4">
        <div class="form-group">
            {{ Form::label( 'sub_cat_id', 'Ledger Account',['class' => 'col control-label']) }}
            <div class='col'>
                <select class="form-control required" name="account_id" id="account_id">
                    
                    @foreach($accounts as $item)
                        @if($item->account_type ==$assetequipments->account_type)
                            <option value="{{$item->id}}" {{ $item->id === @$assetequipments->account_id ? " selected" : "" }}>{{$item->holder}}</option>
                        @endif
                    @endforeach
                </select>
            </div>
        </div>
    </div>
   
</div>

<div class="row">

     <div class="col-md-4">
        <div class='form-group'>
            {{ Form::label( 'condtition', 'Condition Type',['class' => 'col control-label']) }}
            <div class='col'>
                <select class="form-control" name="condtition">
                

                    @if(@$assetequipments->condtition)
                        <option value="{{$assetequipments->condtition}}" selected>{{$assetequipments->condtition}}</option>
                    @endif


                  
                    <option value="New">New</option>
                    <option value="Used">Used</option>
                   
                </select>
            </div>
        </div>
    </div>
      <div class="col-md-4">
        <div class='form-group'>
            {{ Form::label( 'name', 'Item Name',['class' => 'col control-label']) }}
            <div class='col'>
                 {{ Form::text('name', null, ['class' => 'form-control box-size', 'placeholder' => 'Item Name*','required'=>'required']) }}
            </div>
        </div>
    </div>
      <div class="col-md-4">
        <div class='form-group'>
            {{ Form::label( 'serial', 'Item Serial',['class' => 'col control-label']) }}
            <div class='col'>
                 {{ Form::text('serial', null, ['class' => 'form-control box-size', 'placeholder' => 'Item Serial']) }}
            </div>
        </div>
    </div>
    
      
</div>

<div class="row">

     <div class="col-md-4">
        <div class='form-group'>
            {{ Form::label( 'vendor', 'Vendor',['class' => 'col control-label']) }}
            <div class='col'>
                 {{ Form::text('vendor', null, ['class' => 'form-control box-size', 'placeholder' => 'Vendor']) }}
            </div>
        </div>
    </div>


    <div class="col-md-4">
                <div class='form-group'>
                    {{ Form::label( 'cost', 'Cost Price',['class' => 'col control-label']) }}
                    <div class='col'>
                        {{ Form::text('cost', numberFormat(@$assetequipments->cost), ['class' => 'form-control box-size', 'placeholder' => 'Cost Price','onkeypress'=>"return isNumber(event)"]) }}
                    </div>
                </div>
            </div>

    <div class="col-md-4">
                <div class='form-group'>
                    {{ Form::label( 'qty', ' Quantity In Stock',['class' => 'col control-label']) }}
                    <div class='col'>
                        {{ Form::text('qty', numberFormat(@$assetequipments->qty), ['class' => 'form-control box-size', 'placeholder' => ' Quantity In Stock','onkeypress'=>"return isNumber(event)"]) }}
                    </div>
                </div>
            </div>

     
    
      
</div>


<div class="row">

       <div class="col-md-4">
        <div class='form-group'>
            {{ Form::label( 'location', 'Location',['class' => 'col control-label']) }}
            <div class='col'>
                 {{ Form::text('location', null, ['class' => 'form-control box-size', 'placeholder' => 'Location']) }}
            </div>
        </div>
    </div>


 <div class="col-md-4">
        <div class='form-group'>
            {{ Form::label( 'warranty', 'Under Warranty',['class' => 'col control-label']) }}
            <div class='col'>
                <select class="form-control" name="warranty">

                      @if(@$assetequipments->warranty)
                        <option value="{{$assetequipments->warranty}}" selected>{{$assetequipments->warranty}}</option>
                    @endif

                  
                    <option value="No">No</option>
                    <option value="Yes">Yes</option>
                   
                </select>
            </div>
        </div>
    </div>

   <div class="col-md-4">
                <div class='form-group'>
                    {{ Form::label( 'warranty_expiry_date', 'Warranty Expiry Date',['class' => 'col control-label']) }}
                    <div class='col'>
                        {{ Form::text('warranty_expiry_date', dateFormat(@$assetequipments->warranty_expiry_date), ['class' => 'form-control box-size', 'placeholder' => 'Warranty Expiry Date','data-toggle'=>'datepicker','autocomplete'=>'false']) }}
                    </div>
                </div>
            </div>

     
  
      
</div>







@section("after-scripts")
<script type="text/javascript">
        $('[data-toggle="datepicker"]').datepicker({
            autoHide: true,
            format: '{{config('core.user_date_format')}}'
        });

        $(document).on('click', ".add_serial", function (e) {
            e.preventDefault();

            $('#added_product').append('<div class="form-group serial"><label for="field_s" class="col-lg-2 control-label">{{trans('products.product_serial')}}</label><div class="col-lg-10"><input class="form-control box-size" placeholder="{{trans('products.product_serial')}}" name="product_serial[]" type="text"  value=""></div><button class="btn-sm btn-purple v_delete_serial m-1 align-content-end"><i class="fa fa-trash"></i> </button></div>');

        });

        $(document).on('click', ".add_more", function (e) {
            e.preventDefault();
            var product_details = $('#main_product').clone().find(".old_id input:hidden").val(0).end();
            product_details.find(".del_b").append('<button class="btn btn-danger v_delete_temp m-1 align-content-end"><i class="fa fa-trash"></i> </button>').end();
            $('#added_product').append(product_details);
            $('[data-toggle="datepicker"]').datepicker({
                autoHide: true,
                format: '{{config('core.user_date_format')}}'
            });
        });



    </script>


{{ Html::script('focus/js/select2.min.js') }}
    <script type="text/javascript">
        $("#account_id").select2();
        $("#account_type").on('change', function () {
            $("#account_id").val('').trigger('change');
            var tips = $('#account_type :selected').val();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $("#account_id").select2({
                ajax: {
                    url: '{{route('biller.assetequipments.ledger_load')}}?id=' + tips,
                    dataType: 'json',
                    type: 'POST',
                    quietMillis: 50,
                    params: {'cat_id': tips},
                    data: function (product) {
                        return {
                            product: product
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                return {
                                    text: item.holder,
                                    id: item.id
                                }
                            })
                        };
                    },
                }
            });
        });
    </script>
@endsection
