@extends ('core.layouts.app')
@section ('title', trans('business.billing_settings_preference'))
@section('content')

    <div class="content-wrapper">

        <div class="content-body "> {{ Form::open(['route' => 'biller.settings.billing_preference', 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post','files' => true, 'id' => 'create-hrm']) }}

            <div class="row match-height">
                <div class="col-xl-12 col-lg-12">
                    <div class="card">
                        <div class="card-header border-bottom-blue-grey">
                            <h4 class="card-title">{{ trans('business.billing_settings_preference') }}</h4>
                        </div>
                        <div class="card-content">
                            <div class="card-body">
                                <div class='row mb-2'>
                                    {{ Form::label( 'tax', trans('general.default_tax'),['class' => 'col-12 control-label']) }}
                                    <div class='col-md-6'><a class="red" href="{{route('biller.additionals.index')}}"
                                        ><i
                                                    class="ft-plus-circle font-medium-1"></i> <i
                                                    class="ft-arrow-right font-medium-1"></i></a>
                                        <select class="form-control round"
                                                name="tax">
                                            @php
                                                $tax_format='exclusive';
                                                $tax_format_id=0;
                                            @endphp
                                            @foreach($data['additionals'] as $additional_tax)
                                                @php
                                                    if($additional_tax->id == $defaults[4][0]['feature_value']  && $additional_tax->class === 1){
                                                     echo '<option  value="'.$additional_tax->id.'" selected>--'.$additional_tax->name.'--</option>';
                                                     $tax_format=$additional_tax->type2;
                                                     $tax_format_id=$additional_tax->id;
                                                    }

                                                @endphp
                                                {!! $additional_tax->class == 1 ? "<option value='$additional_tax->id'>$additional_tax->name</option>" : "" !!}

                                            @endforeach

                                            <option value="0" data-type1="%" data-type2="off"
                                                    data-type3="off">{{trans('general.off')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class='row mb-2'>
                                    {{ Form::label( 'tax', trans('general.shipping_tax'),['class' => 'col-12 control-label']) }}
                                    <div class='col-md-6'><a class="red" href="{{route('biller.additionals.index')}}"
                                        ><i
                                                    class="ft-plus-circle font-medium-1"></i> <i
                                                    class="ft-arrow-right font-medium-1"></i></a>
                                        <select class="form-control round"
                                                name="ship_tax">
                                            @php
                                                $tax_format='exclusive';
                                                $tax_format_id=0;
                                            @endphp
                                            @foreach($data['additionals'] as $additional_tax)
                                                @php
                                                    if($additional_tax->id == $defaults[4][0]['value2']  && $additional_tax->class === 1){
                                                     echo '<option  value="'.$additional_tax->id.'" selected>--'.$additional_tax->name.'--</option>';
                                                     $tax_format=$additional_tax->type2;
                                                     $tax_format_id=$additional_tax->id;
                                                    }

                                                @endphp
                                                {!! $additional_tax->class == 1 ? "<option value='$additional_tax->id'>$additional_tax->name</option>" : "" !!}

                                            @endforeach

                                            <option value="0" data-type1="%" data-type2="off"
                                                    data-type3="off">{{trans('general.off')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class='row mb-2'>
                                    {{ Form::label( 'discount', trans('general.default_discount'),['class' => 'col-12 control-label']) }}
                                    <div class='col-md-6'><a class="red" href="{{route('biller.additionals.index')}}"
                                        ><i
                                                    class="ft-plus-circle font-medium-1"></i> </a>
                                        <select name="discount" class="form-control round">
                                            @php
                                                $discount_format='%';
$tax_format_id=0;
                                            @endphp
                                            @foreach($data['additionals'] as $additional_discount)
                                                @php
                                                    if($defaults[3][0]['feature_value'] == $additional_discount->id && $additional_discount->class === 2){
                                                     echo '<option value='.$additional_discount->id.' selected>--'.$additional_discount->name.'--</option>';
                                                     $discount_format=$additional_discount->type1;
                                                    }

                                                @endphp
                                                {!! $additional_discount->class == 2 ? "<option value='$additional_discount->id'>$additional_discount->name</option>" : "" !!}
                                            @endforeach

                                        </select>
                                    </div>
                                </div>
                                <div class='row'>
                                    {{ Form::label( 'warehouse', trans('warehouses.warehouse_default'),['class' => 'col-12 control-label']) }}
                                    <div class='col-md-6'><a class="red" href="{{route('biller.warehouses.index')}}"
                                        ><i
                                                    class="ft-plus-circle font-medium-1"></i> </a>
                                        <select name="warehouse"
                                                class="form-control round">

                                            @foreach($data['warehouses'] as $warehouse)
                                                <option value="0">{{trans('general.all')}}</option>
                                                {!! $defaults[1][0]['feature_value']  == $warehouse->id ? "<option value='".$warehouse->id."' selected>--".$warehouse->title."--</option>" : "" !!}
                                                <option value="{{$warehouse->id}}">{{$warehouse->title}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class=''>
                            {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'm-1 btn btn-info btn-md']) }}
                        </div>
                    </div>


                    <!--edit-form-btn-->

                </div>


            </div>


            {{ Form::close() }}
        </div>
    </div>

@endsection
