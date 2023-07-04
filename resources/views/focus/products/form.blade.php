{{-- Product --}}
<h4>{{trans('products.general_product_details')}}</h4>
<div class="row form-group">
    <div class="col-6">
        {{ Form::label( 'name', trans('products.name'),['class' => 'control-label']) }}
        {{ Form::text('name', null, ['class' => 'form-control box-size', 'placeholder' => trans('products.name').'*','required'=>'required']) }}
    </div>
    <div class="col-1">
        {{ Form::label('taxrate', 'Tax %', ['class' => 'control-label']) }}
        {{ Form::text('taxrate', numberFormat(@$produc->taxrate), ['class' => 'form-control box-size', 'placeholder' => trans('products.taxrate'),'onkeypress'=>"return isNumber(event)"]) }}
    </div>
    <div class="col-2">
        {{ Form::label('code_type', trans('products.code_type'), ['class' => 'control-label']) }}
        <select class="custom-select" name="code_type">
            @foreach (['ean13', 'upca', 'ean8', 'issn', 'isbn', 'c128a', 'c39'] as $val) 
                <option value="{{ $val }}" {{ @$product->code_type == $val? 'selected' : '' }}>
                    {{ strtoupper($val) }}
                </option>
            @endforeach       
        </select>       
    </div>
    <div class="col-3">
        {{ Form::label( 'productcategory_id', trans('products.productcategory_id'),['class' => 'control-label']) }}
        <select class="custom-select" name="productcategory_id" id="product_cat">
            @foreach($product_categories as $item)
                @if (!$item->c_type)
                    <option value="{{$item->id}}" {{ $item->id == @$product->productcategory_id ? 'selected' : '' }}>
                        {{$item->title}}
                    </option>
                @endif
            @endforeach
        </select>
    </div>
</div>
<div class="row form-group">
    <div class="col-6">
        {{ Form::label('product_des', trans('products.product_des'),['class' => 'control-label']) }}
        {{ Form::textarea('product_des', null, ['class' => 'form-control col', 'rows'=>2, 'placeholder' => trans('products.product_des')]) }}
    </div>
    
    <div class="col-2">
        {{ Form::label('unit', trans('products.stock_type'),['class' => 'control-label']) }}
        <select class="custom-select" name="stock_type" required>
            @foreach (['general', 'consumable', 'service', ] as $i => $val)
                <option value="{{ $val }}" {{ @$product->stock_type == $val? 'selected' : '' }}>
                    {{ ucfirst($val) }}
                </option>
            @endforeach
        </select>
    </div> 

    <div class="col-2">
        <label for="sku">Stock Keeping Unit (SKU)</label>
        {{ Form::text('sku', null, ['class' => 'form-control']) }}
    </div>

    <div class="col-2">
        {{ Form::label('unit', 'Base Unit', ['class' => 'control-label']) }}
        <select class="custom-select" name="unit_id" id="unit">
            <option value="">-- Choose Base Unit --</option>
            @foreach($productvariables as $item)
                @if ($item->unit_type == 'base')
                    <option value="{{ $item->id }}" {{ $item->id == @$product->unit_id ? 'selected' : '' }} >
                        {{ $item->code }} ({{ $item->title }})
                    </option>    
                @endif
            @endforeach
        </select>
    </div>
</div>
<div class="form-group row">
    <div class="col-6">
        {{ Form::label('unit', 'Compound Unit', ['class' => 'control-label']) }}
        <select class="custom-select" name="compound_unit_id[]" id="compound_unit" data-placeholder="Choose Compound Units" multiple>
            @isset($compound_unit_ids)
                @foreach($productvariables as $item)
                    @if (in_array($item->id, $compound_unit_ids))
                        <option  value="{{ $item->id }}"  selected>
                            {{ $item->code }} ({{ +$item->base_ratio }} units)
                        </option> 
                    @endif
                @endforeach
            @endisset
        </select>
    </div>
</div>
<hr class="mb-3">

{{-- Standard Product Variation --}}
<h4>Standard Product Variation Details</h4>
<div id="main_product">
    <div class="product round">
        <div class="row">
            <div class="col-md-4">
                <div class='form-group'>
                    {{ Form::label( 'purchase_price', 'Product Buying Price',['class' => 'col control-label']) }}
                    <div class='col'>
                        {{ Form::text('purchase_price[]', numberFormat(@$product->standard['purchase_price']), ['class' => 'form-control box-size', 'placeholder' => trans('products.purchase_price'),'onkeypress'=>"return isNumber(event)"]) }}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class='form-group'>
                    {{ Form::label( 'selling_price', 'Minimum Selling Price',['class' => 'col control-label']) }}
                    <div class='col'>
                        {{ Form::text('selling_price[]', numberFormat(@$product->standard['selling_price']), ['class' => 'form-control box-size', 'placeholder' => 'Recommended Selling Price'.'*','required'=>'required','onkeypress'=>"return isNumber(event)"]) }}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class='form-group'>
                    {{ Form::label( 'price', 'Recommended Selling Price',['class' => 'col control-label']) }}
                    <div class='col'>
                        {{ Form::text('price[]', numberFormat(@$product->standard['price']), ['class' => 'form-control box-size', 'placeholder' => 'Product Selling Price'.'*','required'=>'required','onkeypress'=>"return isNumber(event)"]) }}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class='form-group'>
                    {{-- {{ Form::label( 'qty', trans('products.qty'),['class' => 'col control-label']) }} --}}
                    <div class='col'>
                        <input type="hidden" class="form-control box-size" value="{{numberFormat(@$product->standard['qty'] ? @$product->standard['qty'] : '0' ) }}" name="qty[]" @if(isset($product->standard['qty'])) readonly @endif id="" onkeypress="return isNumber(event)">
                        {{-- {{ Form::text('qty[]', numberFormat(@$product->standard['qty']), ['class' => 'form-control box-size','readonly', 'placeholder' => trans('products.qty'),'onkeypress'=>"return isNumber(event)"]) }} --}}
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label( 'productcategory_id', trans('products.warehouse_id'),['class' => 'col control-label']) }}
                    <div class='col'>
                        <select class="custom-select" name="warehouse_id[]">
                            @foreach($warehouses as $item)
                                <option value="{{$item->id}}" {{ $item->id == @$product->standard->warehouse_id ? "selected" : "" }}>
                                    {{$item->title}}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class='form-group'>
                    {{ Form::label('code', trans('products.code'),['class' => 'col control-label']) }}
                    <div class='col'>
                        <input type="text" class="form-control box-size" name="code[]" value="{{ @$product->standard['code']}}" @if(isset($product->standard['code'])) @endif placeholder="{{trans('products.code')}}" id="" readonly>
                        {{-- {{ Form::text('code[]', @$product->standard['code'], ['class' => 'form-control box-size','readonly', 'placeholder' => trans('products.code')]) }} --}}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class='form-group'>
                    {{ Form::label('barcode', trans('products.barcode'),['class' => 'col control-label']) }}
                    <div class='col'>
                        {{ Form::text('barcode[]', @$product->standard['barcode'], ['class' => 'form-control box-size', 'placeholder' => trans('products.barcode')]) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class='form-group'>
                    {{ Form::label('disrate', 'Discount % Rate ',['class' => 'col control-label']) }}
                    <div class='col'>
                        {{ Form::text('disrate[]', numberFormat(@$product->standard['disrate']), ['class' => 'form-control box-size', 'placeholder' => trans('products.disrate'),'onkeypress'=>"return isNumber(event)"]) }}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class='form-group'>
                    {{ Form::label('alert', 'Qty Alert Limit',['class' => 'col control-label']) }}
                    <div class='col'>
                        {{ Form::text('alert[]', numberFormat(@$product->standard['alert']), ['class' => 'form-control box-size', 'placeholder' => trans('products.alert'),'onkeypress'=>"return isNumber(event)"]) }}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class='form-group'>
                    {{ Form::label( 'expiry', trans('products.expiry'),['class' => 'col control-label']) }}
                    <div class='col'>
                        {{ Form::text('expiry[]', dateFormat(@$product->standard['expiry']), ['class' => 'form-control datepicker']) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-1 mb-1">
            <div class="col-md-12">
                <div class='form-group'>
                    {{ Form::label('variation_name', 'Standard Product Description',['class' => 'col control-label']) }}
                    <div class='col-6'>
                        {{ Form::text('variation_name[]', @$product->standard['name'], ['class' => 'form-control box-size', 'placeholder' => 'Variation Description', 'required']) }}
                    </div>
                </div>
            </div>
            <div class="old_id"><input type="hidden" name="v_id[]" value="{{@$product->standard['id']}}"><input type="hidden" name="pv_id" value="{{@$product->standard['id']}}"></div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class='form-group'>
                    {{ Form::label( 'image', trans('products.image'),['class' => 'col control-label']) }}
                    <div class='col'>
                        {!! Form::file('image[]', array('class'=>'input' )) !!}
                    </div>
                </div>
            </div>
        </div>
        <span class="col-6 del_b"></span>
        <hr>
    </div>
</div>

@if(isset($product->standard->product_serial))
    @foreach($product->standard->product_serial as $serial)
        <div class="form-group serial"><label for="field_s" class="col-lg-2 control-label">{{trans('products.product_serial')}}</label>
            <div class="col-lg-10"><input class="form-control box-size" placeholder="{{trans('products.product_serial')}}" name="product_serial_e[{{$serial['id']}}]" type="text" value="{{$serial['value']}}" @if($serial['value2']) readonly="" @endif></div>
        </div>
    @endforeach
@endif

{{-- Additional Product Variations --}}
@if(isset($product->standard))
    <h4 class="card-title mt-3">{{trans('products.variation')}}</h4>
    <div id="product_sub">
        @foreach($product->variations as $i => $row)
            @php
                // exclude standard product
                if (!$i) continue;
            @endphp
            <div class="v_product_t border-blue-grey border-lighten-4 round p-1 bg-blue-grey bg-lighten-5" id="pv_{{$row->id}}">
                <input type="hidden" id="" name="v_id[]" value="{{$row->id}}">
                <div class="row mt-3 mb-3">
                    <div class="col-6">{{trans('general.description')}} 
                        <input type="text" class="form-control " name="variation_name[]" value="{{$row->name}}" placeholder="{{trans('general.description')}}">
                    </div>
                    <div class="del_b offset-4 col-1" data-vid="{{$row->id}}">
                        <button class="btn btn-danger v_delete m-1 align-content-end"><i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class='form-group'>
                            {{ Form::label( 'price', trans('products.price'),['class' => 'col control-label']) }}
                            <div class='col'>
                                {{ Form::text('price[]', numberFormat(@$row->price), ['class' => 'form-control box-size', 'placeholder' => trans('products.price'),'onkeypress'=>"return isNumber(event)"]) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class='form-group'>
                            {{ Form::label( 'purchase_price', trans('products.purchase_price'),['class' => 'col control-label']) }}
                            <div class='col'>
                                {{ Form::text('purchase_price[]', numberFormat(@$row->purchase_price), ['class' => 'form-control box-size', 'placeholder' => trans('products.purchase_price'),'onkeypress'=>"return isNumber(event)"]) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class='form-group'>
                            {{ Form::label( 'qty', trans('products.qty'),['class' => 'col control-label']) }}
                            <div class='col'>
                                {{ Form::text('qty[]', numberFormat(@$row->qty), ['class' => 'form-control box-size','readonly', 'placeholder' => trans('products.qty'),'onkeypress'=>"return isNumber(event)"]) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            {{ Form::label( 'productcategory_id', trans('products.warehouse_id'),['class' => 'col control-label']) }}
                            <div class='col'>
                                <select class="form-control" name="warehouse_id[]">
                                    @foreach($warehouses as $item)
                                    <option value="{{$item->id}}" {{ $item->id === @$row->warehouse_id ? " selected" : "" }}>{{$item->title}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class='form-group'>
                            {{ Form::label( 'code', trans('products.code'),['class' => 'col control-label']) }}
                            <div class='col'>
                                {{ Form::text('code[]', @$row->code, ['class' => 'form-control box-size','placeholder' => trans('products.code')]) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class='form-group'>
                            {{ Form::label( 'barcode', trans('products.barcode'),['class' => 'col control-label']) }}
                            <div class='col'>
                                {{ Form::text('barcode[]', $row->barcode, ['class' => 'form-control box-size', 'placeholder' => trans('products.barcode')]) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class='form-group'>
                            {{ Form::label( 'disrate', trans('products.disrate'),['class' => 'col control-label']) }}
                            <div class='col'>
                                {{ Form::text('disrate[]', numberFormat(@$row->disrate), ['class' => 'form-control box-size', 'placeholder' => trans('products.disrate'),'onkeypress'=>"return isNumber(event)"]) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class='form-group'>
                            {{ Form::label( 'alert', trans('products.alert'),['class' => 'col control-label']) }}
                            <div class='col'>
                                {{ Form::text('alert[]', numberFormat(@$row->alert), ['class' => 'form-control box-size', 'placeholder' => trans('products.alert'),'onkeypress'=>"return isNumber(event)"]) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class='form-group'>
                            {{ Form::label( 'expiry', trans('products.expiry'),['class' => 'col control-label']) }}
                            <div class='col'>
                                {{ Form::text('expiry[]', dateFormat(@$row->expiry), ['class' => 'form-control box-size', 'placeholder' => trans('products.expiry')]) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class='form-group'>
                            {{ Form::label( 'image', trans('products.image'),['class' => 'col control-label']) }}
                            <div class='col'>
                                {!! Form::file('image[]', array('class'=>'input' )) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

<div id="added_product"></div>
<a href="#" class="card-title "><i class="fa fa-plus-circle"></i> {{trans('products.variation')}}</a>
<button class="btn btn-blue add_more btn-sm m-1">{{trans('general.add_row')}}</button>
<button class="btn btn-pink add_serial btn-sm m-1">{{trans('products.add_serial')}}</button>
<div id="remove_variation"></div>

@section("after-styles")
<style>
    #added_product div:nth-child(even) .product {
        background: #FFF
    }

    #added_product div:nth-child(odd) .product {
        background: #eeeeee
    }

    #product_sub div:nth-child(odd) .v_product_t {
        background: #FFF
    }

    #product_sub div:nth-child(even) .v_product_t {
        background: #eeeeee
    }
</style>
{!! Html::style('focus/css/select2.min.css') !!}
@endsection

@section("after-scripts")
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    const config = {
        datepicker: {
            autoHide: true,
            format: "{{config('core.user_date_format')}}"
        },
    };
   
    const Form = {
        units: @json($productvariables),
        init() {
            $('.datepicker').datepicker(config.datepicker);
            $('#compound_unit').select2();

            $('#unit').change(this.unitChange);
            const events = [".add_more", ".add_serial", ".v_delete", ".v_delete_temp", ".v_delete_serial"];
            const handlers = [this.addMore, this.addSerial, this.delVariableProduct, this.delProduct, this.delSerial];
            events.forEach((v,i) => $(document).on('click', v, handlers[i]));
        },

        unitChange() {
            const el = $(this);
            const compoundUnits = Form.units.filter(v => v.base_unit_id == el.val())
            .map(v => ({id: v.id, text: `${v.code} (${parseFloat(v.base_ratio)} units)`}));
            $('#compound_unit option').remove();
            $('#compound_unit').select2({data: compoundUnits});
        },

        addMore(e) {
            e.preventDefault();
            var product_details = $('#main_product').clone().find(".old_id input:hidden").val(0).end();
            product_details.find(".del_b").append('<button class="btn btn-danger v_delete_temp m-1 align-content-end"><i class="fa fa-trash"></i> </button>').end();
            $('#added_product').append(product_details);
            $('.datepicker').datepicker(config.datepicker);
        },

        delVariableProduct(e) {
            e.preventDefault();
            var p_v = $(this).closest('div').attr('data-vid');
            $('#remove_variation').append("<input type='hidden' name='remove_v[]' value='" + p_v + "'>");
            alert("{{trans('products.alert_removed')}}");
            $('#pv_' + p_v).remove();        
        },

        delProduct(e) {
            e.preventDefault();
            $(this).closest('div .product').remove();
        },

        addSerial(e) {
            e.preventDefault();
            $('#added_product').append(
                `<div class="form-group serial"><label for="field_s" class="col-lg-2 control-label">{{trans('products.product_serial')}}</label><div class="col-lg-10">
                <input class="form-control box-size" placeholder="{{trans('products.product_serial')}}" name="product_serial[]" type="text"  value=""></div>
                <button class="btn-sm btn-purple v_delete_serial m-1 align-content-end"><i class="fa fa-trash"></i> </button></div>`
            );
        },

        delSerial(e) {
            e.preventDefault();
            $(this).closest('div .serial').remove();
        },
    };

    $(() => Form.init());
</script>
@endsection