@extends('core.layouts.app')
@section('title', trans('labels.backend.quotes.management') . ' | ' . trans('labels.backend.quotes.create'))

@section('after-styles')
{!! Html::style('focus/jq_file_upload/css/jquery.fileupload.css') !!}
@endsection

@section('page-header')
<h1>
    {{ trans('labels.backend.quotes.management') }}
    <small>{{ trans('labels.backend.quotes.create') }}</small>
</h1>
@endsection

@section('content')
<div class="app-content m-1">
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-2">
                <h4 class="content-header-title mb-0">{{ trans('labels.backend.quotes.management') }}</h4>
            </div>
            <div class="content-header-right col-md-6 col-12">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        @include('focus.quotes.partials.quotes-header-buttons')
                    </div>
                </div>
            </div>
        </div>
        
        <div class="content-body">
            <section class="card">
                <div id="invoice-template" class="card-body">                    
                    @include('focus.quotes.partials.view_menu')                    
                    @if ($quote['verified'] == "Yes")
                        <div class="badge text-center white d-block m-1">
                            <span class="bg-danger round p-1">
                                &nbsp;&nbsp;This Quote/PI has been verified&nbsp;&nbsp;
                            </span>
                        </div>
                    @endif
                    <!-- Invoice Company Details -->
                    <div id="invoice-company-details" class="row">
                        <div class="col-md-6 col-sm-12 text-center text-md-left">{{trans('general.our_info')}}
                            <div class="">
                                <img src="{{ Storage::disk('public')->url('app/public/img/company/' . config('core.logo')) }}" alt="company logo" class="avatar-100 img-responsive" />
                                <div class="media-body"><br>
                                    <ul class="px-0 list-unstyled">
                                        <li class="text-bold-800">{{(config('core.cname'))}}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12 text-center text-md-right">
                            @if ($quote->bank_id)
                                <h2>Proformer Invoice</h2>
                                <p class="pb-3">#PI {{ $quote->tid }}</p>
                            @else
                                <h2>{{ trans('quotes.quote')}}</h2>
                                <p class="pb-3">#{{ prefix(5) }} {{ $quote['tid'] }}</p>
                            @endif
                            <ul class="px-0 list-unstyled">
                                <li>{{trans('general.total')}}</li>
                                <li class="lead text-bold-800">{{amountFormat($quote['total'])}}</li>
                            </ul>
                        </div>
                    </div>
                    <!--/ Invoice Company Details -->
                    <!-- Invoice Customer Details -->
                    <div id="invoice-customer-details" class="row pt-2">
                        <div class="col-sm-12 text-center text-md-left">
                            <p class="text-muted">{{trans('invoices.bill_to')}}</p>
                        </div>
                        <div class="col-md-6 col-sm-12 text-center text-md-left">
                            @php
                                if ($quote->lead_id) {
                                    if($quote->lead->client_status == 'customer') {
                                        $customername = $quote->client->name ;
                                        $branchname = $quote->branch->name ;
                                        $adrress = $quote->client->address;
                                        $email = @$quote->client->email ;
                                        $cell = $quote->client->phone;
                                    } else {
                                        $customername = $quote->lead->client_name;
                                        $branchname = "";
                                        $adrress = "";
                                        $email = $quote->lead->client_email;
                                        $cell = $quote->lead->client_contact; 
                                    }
                                } else {
                                    if ($quote->customer_id) {
                                        $customername = $quote->customer->name ;
                                        $branchname = $quote->customer_branch->name ;
                                        $adrress = $quote->customer->address;
                                        $email = @$quote->customer->email ;
                                        $cell = $quote->customer->phone;
                                    } else {
                                        $customername = $quote->client_name;
                                        $branchname = "";
                                        $adrress = "";
                                        $email = $quote->client_email;
                                        $cell = $quote->client_contact;
                                    }
                                }
                            @endphp
                            <ul class="px-0 list-unstyled">
                                <li>{{$customername}},</li>
                                <li>{{$branchname}},</li>
                                <li>{{$adrress}},</li>
                                <li>{{$email}},</li>
                                <li>{{$cell}},</li>
                                @if ($quote->customer)
                                    {!! custom_fields_view(1, $quote->customer->id, false) !!}
                                @endif
                            </ul>
                        </div>
                        <div class="col-md-6 col-sm-12 text-center text-md-right">
                            <p>
                                <span class="text-muted">{{trans('quotes.invoicedate')}} :</span> {{dateFormat($quote['invoicedate'])}}
                            </p>
                            <p>
                                <span class="text-muted">{{trans('quotes.invoiceduedate')}} :</span> {{dateFormat($quote['invoiceduedate'])}}
                            </p>
                            <div class="row">
                                <div class="col">
                                    <hr>
                                    <p class=" text-danger">{{$quote['notes']}}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <hr>
                            <p>{!! $quote['proposal'] !!}</p>
                        </div>
                    </div>
                    <!--/ Invoice Customer Details -->
                    <!-- Invoice Items Details -->
                    <div id="invoice-items-details" class="pt-2">
                        <div class="row">
                            <div class="table-responsive col-sm-12">
                                <table class="table">
                                    @if ($quote['tax_format']=='exclusive' OR $quote['tax_format']=='inclusive')
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>{{trans('products.product_des')}}</th>
                                                <th class="text-right">{{trans('products.price')}}</th>
                                                <th class="text-right">{{trans('products.qty')}}</th>
                                                <th class="text-right">{{trans('general.tax')}}</th>
                                                <th class="text-right">{{trans('general.discount')}}</th>
                                                <th class="text-right">{{trans('general.subtotal')}}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($products as $product)
                                                @if ($product['a_type'] == 1)                                               
                                                    <tr>
                                                        <th scope="row">{{ $product['numbering'] }}</th>
                                                        <td>
                                                            <p>{{$product['product_name']}}</p>
                                                            <p class="text-muted"> {!!$product['product_des'] !!} </p>
                                                        </td>
                                                        <td class="text-right">{{amountFormat($product['product_price'])}}</td>
                                                        <td class="text-right">{{numberFormat($product['product_qty'])}} {{$product['unit']}}</td>
                                                        <td class="text-right">
                                                            @php
                                                                $price = (float) $product->product_price;
                                                                $subtotal = (float) $product->product_subtotal;
                                                                $tax_amount = amountFormat($subtotal - $price);
                                                            @endphp
                                                            {{ $tax_amount }}
                                                            <span class="font-size-xsmall">({{numberFormat($product['product_tax'])}}%)</span>
                                                        </td>
                                                        <td class="text-right">{{amountFormat($product['total_discount'])}}</td>
                                                        <td class="text-right">{{amountFormat($product['product_subtotal'])}}</td>
                                                    </tr>
                                                @elseif ($product['a_type'] == 2)
                                                    <tr>
                                                        <th scope="row">{{ $product['numbering'] }}</th>
                                                        <td>
                                                            <p>{{$product['product_name']}}</p>
                                                        </td>
                                                        <td class="text-right"></td>
                                                        <td class="text-right"></td>
                                                        <td class="text-right"> </td>
                                                        <td class="text-right"></td>
                                                        <td class="text-right"></td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                            <tr><td colspan="7"></td></tr>
                                        </tbody>
                                    @endif

                                    @if ($quote['tax_format'] == 'cgst')
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>{{trans('products.product_des')}}</th>
                                                <th class="text-right">{{trans('products.price')}}</th>
                                                <th class="text-right">{{trans('products.qty')}}</th>
                                                <th class="text-right">{{trans('general.cgst')}}</th>
                                                <th class="text-right">{{trans('general.sgst')}}</th>
                                                <th class="text-right">{{trans('general.discount')}}</th>
                                                <th class="text-right">{{trans('general.subtotal')}}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($quote->products as $product)
                                                <tr>
                                                    <th scope="row">{{ $loop->iteration }}</th>
                                                    <td>
                                                        <p>{{$product['product_name']}}</p>
                                                        <p class="text-muted"> {!!$product['product_des'] !!} </p>
                                                    </td>
                                                    <td class="text-right">{{amountFormat($product['product_price'])}}</td>
                                                    <td class="text-right">{{numberFormat($product['product_qty'])}} {{$product['unit']}}</td>
                                                    <td class="text-right">{{ amountFormat($product['product_tax']/2) }}
                                                        <span class="font-size-xsmall">({{numberFormat($product['product_tax']/2)}}%)</span>
                                                    </td>
                                                    <td class="text-right">{{amountFormat($product['total_discount'])}}</td>
                                                    <td class="text-right">{{amountFormat($product['product_subtotal'])}}</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="8">{!! custom_fields_view(3,$product['product_id'],false) !!}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    @endif
                                    @if ($quote['tax_format'] == 'igst')
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>{{trans('products.product_des')}}</th>
                                                <th class="text-right">{{trans('products.price')}}</th>
                                                <th class="text-right">{{trans('products.qty')}}</th>
                                                <th class="text-right">{{trans('general.igst')}}</th>
                                                <th class="text-right">{{trans('general.discount')}}</th>
                                                <th class="text-right">{{trans('general.subtotal')}}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($quote->products as $product)
                                                <tr>
                                                    <th scope="row">{{ $loop->iteration }}</th>
                                                    <td>
                                                        <p>{{$product['product_name']}}</p>
                                                        <p class="text-muted"> {!!$product['product_des'] !!} </p>
                                                    </td>
                                                    <td class="text-right">{{amountFormat($product['product_price'])}}</td>
                                                    <td class="text-right">{{numberFormat($product['product_qty'])}} {{$product['unit']}}</td>
                                                    <td class="text-right">{{amountFormat($product['total_tax'])}} <span class="font-size-xsmall">({{numberFormat($product['product_tax'])}}%)</span>
                                                    </td>
                                                    <td class="text-right">{{amountFormat($product['total_discount'])}}</td>
                                                    <td class="text-right">{{amountFormat($product['product_subtotal'])}}</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="7">{!! custom_fields_view(4,$product['product_id'],false) !!}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    @endif
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-7">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-borderless table-md text-bold-600">
                                            <tbody>
                                                <tr>
                                                    <td>{{trans('quotes.status')}}:</td>
                                                    <td id="status" class="badge badge-info">{{trans('payments.'.$quote['status'])}}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5 col-sm-12">
                                <p class="lead">{{trans('general.summary')}}</p>
                                <div class="table-responsive">
                                    <table class="table">
                                        <tbody>
                                            <tr>
                                                <td>{{trans('general.subtotal')}}</td>
                                                <td class="text-right">{{amountFormat($quote['subtotal'])}}</td>
                                            </tr>
                                            @if($quote['tax']>0)
                                            <tr>
                                                <td>{{trans('general.tax')}}</td>
                                                <td class="text-right">{{amountFormat($quote['tax'])}}</td>
                                            </tr>@endif
                                            @if($quote['discount']>0)
                                            <tr>
                                                <td>{{trans('general.discount')}}</td>
                                                <td class="text-right">{{amountFormat($quote['discount'])}}</td>
                                            </tr>@endif
                                            @if($quote['shipping']>0)
                                            <tr>
                                                <td>{{trans('general.shipping')}}</td>
                                                <td class="text-right">{{amountFormat($quote['shipping'])}}</td>
                                            </tr>
                                            @if($quote['ship_tax']>0)
                                            <tr>
                                                <td>{{trans('general.shipping_tax')}}
                                                    ({{trans('general.'.$quote['ship_tax_type'])}})
                                                </td>
                                                <td class="text-right">{{amountFormat($quote['ship_tax'])}}</td>
                                            </tr>@endif
                                            @endif
                                            <tr>
                                                <td class="text-bold-800">{{trans('general.total')}}</td>
                                                <td class="text-bold-800 text-right">{{amountFormat($quote['total'])}}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-center">
                                    <p>{{trans('general.authorized_person')}}</p>
                                    <img src="{{ Storage::disk('public')->url('app/public/img/signs/' . $quote->user->signature) }}" alt="signature" class="height-100 m-2" />
                                    <h6>{{$quote->user->first_name}} {{$quote->user->last_name}}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    {!! custom_fields_view(2,$quote['id']) !!}
                    <!-- Invoice Footer -->
                    <div id="invoice-footer">
                        <div class="row">
                            <div class="col-md-7 col-sm-12">
                                <h5>Approvals</h5>
                                <hr>
                                <h5> <span class="text-muted">Approved By :</span> <span class=" text-danger">{{$quote['approved_by']}}</span> <span class="text-muted">Approval Date :</span> <span class=" text-danger">{{dateFormat($quote['approved_date'])}}</span> <span class="text-muted">Approval Method :</span><span class=" text-danger">{{$quote['approved_method']}}</span></h5>
                                <p><span class="text-muted">LPO Date :</span> <span class=" text-danger">{{dateFormat($quote['lpo_date'])}}</span> <span class="text-muted">LPO Number :</span> <span class=" text-danger">{{$quote['lpo_number']}}</span> <span class="text-muted">LPO Amount :</span><span class=" text-danger">{{numberFormat($quote['lpo_amount'])}}</span></p>
                            </div>
                            <div class="col-md-7 col-sm-12">
                                <h5>{{trans('general.payment_terms')}}</h5>
                                <hr>
                                <h5>{{@$quote->term->title}}</h5>
                                <p>{!! @$quote->term->terms !!}</p>
                            </div>
                            <div class="col-md-5 col-sm-12 text-center">
                                @if($quote['status']!='canceled') <a href="#sendEmail" data-toggle="modal" data-remote="false" data-type="6" data-type1="proposal" class="btn btn-primary btn-lg my-1 send_bill"><i class="fa fa-paper-plane-o"></i> {{trans('general.send')}}
                                </a>@endif
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <p class="lead">{{trans('general.attachment')}}</p>
                            <pre>{{trans('general.allowed')}}: {{$features['value1']}} </pre>
                            <!-- The fileinput-button span is used to style the file input field as button -->
                            <div class="btn btn-success fileinput-button display-block col-2">
                                <i class="glyphicon glyphicon-plus"></i>
                                <span>Select files...</span>
                                <!-- The file input field used as target for the file upload widget -->
                                <input id="fileupload" type="file" name="files">
                            </div>
                        </div>
                    </div>
                    <!-- The global progress bar -->
                    <div id="progress" class="progress progress-sm mt-1 mb-0 col-md-3">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <!-- The container for the uploaded files -->
                    <table id="files" class="files table table-striped mt-2">
                        @foreach($quote->attachment as $row)
                        <tr>
                            <td><a data-url="{{route('biller.bill_attachment')}}?op=delete&id={{$row['id']}}" class="aj_delete red"><i class="btn-sm fa fa-trash"></i></a> <a href="{{ Storage::disk('public')->url('app/public/files/' . $row['value']) }}" class="purple"><i class="btn-sm fa fa-eye"></i> {{$row['value']}}</a>
                            </td>
                        </tr>
                        @endforeach
                    </table>
                    <br>
                </div>
            </section>
        </div>
    </div>
</div>
@php 
    $invoice=$quote; 
@endphp
@include("focus.modal.quote_status_model")
@include('focus.modal.email_model', array('category'=>4))
@include('focus.modal.sms_model', array('category'=>4))
@include("focus.modal.cancel_model")
@include("focus.modal.convert_model")
@include("focus.modal.lpo_model")
@endsection

@section('extra-scripts')
{{ Html::script('focus/jq_file_upload/js/jquery.fileupload.js') }}

<script type="text/javascript">
    $('[data-toggle="datepicker"]')
        .datepicker({ format: "{{ config('core.user_date_format') }}" })
        .datepicker('setDate', new Date());

    $(function() {
        $('.summernote').summernote({
            height: 150,
            toolbar: [
                // [groupName, [list of button]]
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']],
                ['fullscreen', ['fullscreen']],
                ['codeview', ['codeview']]
            ],
            popover: {}
        });

        /*jslint unparam: true */
        /*global window, $ */
        $(function() {
            'use strict';
            // Change this to the location of your server-side upload handler:
            var url = "{{route('biller.bill_attachment')}}";
            $('#fileupload').fileupload({
                url: url,
                dataType: 'json',
                formData: {
                    _token: "{{ csrf_token() }}",
                    id: "{{$quote['id ']}}",
                    'bill': 4
                },
                done: function(e, data) {
                    $.each(data.result, function(index, file) {
                        $('#files').append('<tr><td><a data-url="{{route('biller.bill_attachment')}}?op=delete&id= ' + file.id + ' " class="aj_delete red"><i class="btn-sm fa fa-trash"></i></a> ' + file.name + ' </td></tr>');
                    });
                },
                progressall: function(e, data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    $('#progress .progress-bar').css(
                        'width',
                        progress + '%'
                    );
                }
            }).prop('disabled', !$.support.fileInput)
            .parent().addClass($.support.fileInput ? undefined : 'disabled');
        });

        $(document).on('click', ".aj_delete", function(e) {
            e.preventDefault();
            var aurl = $(this).attr('data-url');
            var obj = $(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: aurl,
                type: 'POST',
                dataType: 'json',
                success: function(data) {
                    obj.closest('tr').remove();
                    obj.remove();
                }
            });
        });
    });
</script>
@endsection
