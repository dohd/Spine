@extends ('core.layouts.app')

@section ('title', trans('labels.backend.purchaseorders.management') . ' | ' . trans('labels.backend.purchaseorders.create'))

@section('page-header')
    <h1>
        {{ trans('labels.backend.purchaseorders.management') }}
        <small>{{ trans('labels.backend.purchaseorders.create') }}</small>
    </h1>
@endsection



@section('content')
    <div class="app-content m-1">
        <div class="content-wrapper">

            <div class="content-body">
                <section class="card">
                    <div id="invoice-template" class="card-body">
                        <div class="row">
                            @if($purchaseorder['status']!='canceled')
                                <div class="col">
                                    <a href="{{$purchaseorder['id']}}/edit" class="btn btn-warning mb-1"><i
                                                class="fa fa-pencil"></i> {{trans('general.edit')}}</a>

                                    <a href="#modal_bill_payment_1" data-toggle="modal" data-remote="false"
                                       data-type="reminder"
                                       class="btn btn-large btn-info mb-1" title="Partial Payment"><span
                                                class="fa fa-money"></span> {{trans('general.make_payment')}} </a>

                                    <div class="btn-group">
                                        <button type="button" class="btn btn-facebook dropdown-toggle mb-1"
                                                data-toggle="dropdown"
                                                aria-haspopup="true" aria-expanded="false">
                            <span
                                    class="fa fa-envelope-o"></span> {{trans('customers.email')}}
                                        </button>
                                        <div class="dropdown-menu"><a href="#sendEmail" data-toggle="modal"
                                                                      data-remote="false"
                                                                      class="dropdown-item send_bill"
                                                                      data-type="8"
                                                                      data-type1="notification">{{trans('orders.notification')}}</a>

                                        </div>

                                    </div>

                                    <!-- SMS -->
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-blue dropdown-toggle mb-1"
                                                data-toggle="dropdown"
                                                aria-haspopup="true" aria-expanded="false">
                            <span
                                    class="fa fa-mobile"></span> {{trans('general.sms')}}
                                        </button>
                                        <div class="dropdown-menu"><a href="#sendSMS" data-toggle="modal"
                                                                      data-remote="false" class="dropdown-item send_sms"
                                                                      data-type="18"
                                                                      data-type1="notification">{{trans('orders.notification')}}</a>

                                        </div>

                                    </div>

                                    @php
                                        $valid_token = token_validator('','po' . $purchaseorder['id'].$purchaseorder['tid'],true);
                                        $link=route( 'biller.print_bill',[$purchaseorder['id'],9,$valid_token,1]);
                                        $link_download=route( 'biller.print_bill',[$purchaseorder['id'],9,$valid_token,2]);
                                        $link_preview=route( 'biller.view_bill',[$purchaseorder['id'],9,$valid_token,0]);
                                    @endphp

                                    <div class="btn-group ">
                                        <button type="button" class="btn btn-success mb-1 btn-min-width dropdown-toggle"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i
                                                    class="fa fa-print"></i> {{trans('general.print')}}
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item"
                                               href="{{$link}}">{{trans('general.print')}}</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item"
                                               href="{{$link_download}}">{{trans('general.pdf')}}</a>

                                        </div>
                                    </div>
                                    <a href="{{$link_preview}}" class="btn btn-blue-grey mb-1"><i
                                                class="fa fa-globe"></i> {{trans('general.preview')}}
                                    </a>

                                    <a href="#pop_model_1" data-toggle="modal" data-remote="false"
                                       class="btn btn-large btn-cyan mb-1" title="Change Status"
                                    ><span class="fa fa-retweet"></span> {{trans('general.change_status')}}</a>
                          {{--      <a href="#pop_model_2" class="btn btn-danger mb-1" data-toggle="modal"
                                       data-remote="false"><i
                                                class="fa fa-minus-circle"> </i> {{trans('general.cancel')}}
                                    </a> --}}


                                </div>
                            @else
                                <div class="badge text-center white d-block m-1"><span class="bg-danger round p-1">&nbsp;&nbsp;{{trans('payments.'.$purchaseorder['status'])}}&nbsp;&nbsp;</span>
                                </div>
                            @endif
                        </div>
                        @if($purchaseorder['currency'])
                            <div class="badge text-center white d-block m-1"><span class="bg-danger round p-1">&nbsp;&nbsp;{{trans('general.different_currency')}}&nbsp;&nbsp;</span>
                            </div>
                    @endif

                    <!-- Invoice Company Details -->
                        <div id="invoice-company-details" class="row">
                            <div class="col-md-6 col-sm-12 text-center text-md-left">{{trans('general.our_info')}}
                                <div class="">
                                    <img src="{{ Storage::disk('public')->url('app/public/img/company/' . config('core.logo')) }}"
                                         alt="company logo" class="avatar-100 img-responsive"/>
                                    <div class="media-body"><br>
                                        <ul class="px-0 list-unstyled">
                                            <li class="text-bold-800">{{(config('core.cname'))}}</li>

                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12 text-center text-md-right">
                                <h2>{{trans('purchaseorders.purchaseorder')}}</h2>
                                <p class="pb-3">{{prefix(9)}} # {{$purchaseorder['tid']}}</p>
                                <ul class="px-0 list-unstyled">
                                    <li>{{trans('general.total')}}</li>
                                    <li class="lead text-bold-800">{{amountFormat($purchaseorder['total'])}}</li>
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
                                <ul class="px-0 list-unstyled">
                                    <li class="text-bold-800"><a
                                                href="{{route('biller.customers.show',[$purchaseorder->supplier->id])}}">{{$purchaseorder->supplier->name}}</a>
                                    </li>
                                    <li>{{$purchaseorder->supplier->address}},</li>
                                    <li>{{$purchaseorder->supplier->city}},{{$purchaseorder->supplier->region}}</li>
                                    <li>{{$purchaseorder->supplier->country}}-{{$purchaseorder->supplier->postbox}}.
                                    </li>
                                    <li>{{$purchaseorder->supplier->email}},</li>
                                    <li>{{$purchaseorder->supplier->phone}},</li>

                                </ul>
                            </div>

                            <div class="col-md-6 col-sm-12 text-center text-md-right">
                                <p>
                                    <span class="text-muted">{{trans('invoices.invoice_date')}} :</span> {{dateFormat($purchaseorder['invoicedate'])}}
                                </p>

                                <p>
                                    <span class="text-muted">{{trans('invoices.invoice_due_date')}} :</span> {{dateFormat($purchaseorder['invoiceduedate'])}}
                                </p>
                                <div class="row">
                                    <div class="col">

                                        <hr>

                                        <p class=" text-danger">{{$purchaseorder['notes']}}</p>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!--/ Invoice Customer Details -->
                        <!-- Invoice Items Details -->
                        <div id="invoice-items-details" class="pt-2">
                            <div class="row">
                                <div class="table-responsive col-sm-12">

                                    <table class="table">
                                        @if($purchaseorder['tax_format']=='exclusive' OR $purchaseorder['tax_format']=='inclusive')
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

                                            @foreach($purchaseorder->products as $product)
                                                <tr>
                                                    <th scope="row">{{ $loop->iteration }}</th>
                                                    <td>
                                                        <p>{{$product['product_name']}}</p>
                                                        <p class="text-muted"> {!!$product['product_des'] !!} </p>
                                                    </td>
                                                    <td class="text-right">{{amountFormat($product['product_price'])}}</td>
                                                    <td class="text-right">{{numberFormat($product['product_qty'])}} {{$product['unit']}}</td>
                                                    <td class="text-right">{{amountFormat($product['total_tax'])}} <span
                                                                class="font-size-xsmall">({{numberFormat($product['product_tax'])}}%)</span>
                                                    </td>
                                                    <td class="text-right">{{amountFormat($product['total_discount'])}}</td>
                                                    <td class="text-right">{{amountFormat($product['product_subtotal'])}}</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="7">{!! custom_fields_view(3,$product['product_id'],false) !!}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        @endif

                                        @if($purchaseorder['tax_format']=='cgst')
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

                                            @foreach($purchaseorder->products as $product)
                                                <tr>
                                                    <th scope="row">{{ $loop->iteration }}</th>
                                                    <td>
                                                        <p>{{$product['product_name']}}</p>
                                                        <p class="text-muted"> {!!$product['product_des'] !!} </p>
                                                    </td>
                                                    <td class="text-right">{{amountFormat($product['product_price'])}}</td>
                                                    <td class="text-right">{{numberFormat($product['product_qty'])}} {{$product['unit']}}</td>
                                                    <td class="text-right">{{amountFormat($product['total_tax']/2)}}
                                                        <span class="font-size-xsmall">({{numberFormat($product['product_tax']/2)}}%)</span>
                                                    </td>
                                                    <td class="text-right">{{amountFormat($product['total_tax']/2)}}
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

                                        @if($purchaseorder['tax_format']=='igst')
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

                                            @foreach($purchaseorder->products as $product)
                                                <tr>
                                                    <th scope="row">{{ $loop->iteration }}</th>
                                                    <td>
                                                        <p>{{$product['product_name']}}</p>
                                                        <p class="text-muted">{{$product['product_des']}}</p>
                                                    </td>
                                                    <td class="text-right">{{amountFormat($product['product_price'])}}</td>
                                                    <td class="text-right">{{numberFormat($product['product_qty'])}} {{$product['unit']}}</td>
                                                    <td class="text-right">{{amountFormat($product['total_tax'])}} <span
                                                                class="font-size-xsmall">({{numberFormat($product['product_tax'])}}%)</span>
                                                    </td>
                                                    <td class="text-right">{{amountFormat($product['total_discount'])}}</td>
                                                    <td class="text-right">{{amountFormat($product['product_subtotal'])}}</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="7">{!! custom_fields_view(3,$product['product_id'],false) !!}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        @endif
                                    </table>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-7 col-sm-12 text-center text-md-left">
                                    <p class="lead">{{trans('payments.payment_status')}}:</p>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-borderless table-md">
                                                <tbody>
                                                <tr>
                                                    <td>{{trans('payments.payment_status')}}:</td>
                                                    <td id="status">{{trans('payments.'.$purchaseorder['status'])}}</td>
                                                </tr>
                                                @if($purchaseorder['pmethod'])
                                                    <tr>
                                                        <td>{{trans('general.payment_method')}}:</td>
                                                        <td id="method">{{$purchaseorder['pmethod']}}</td>
                                                    </tr>
                                                @endif


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
                                                <td class="text-right">{{amountFormat($purchaseorder['subtotal'])}}</td>
                                            </tr>
                                            @if($purchaseorder['tax']>0)
                                                <tr>
                                                    <td>{{trans('general.tax')}}</td>
                                                    <td class="text-right">{{amountFormat($purchaseorder['tax'])}}</td>
                                                </tr>@endif
                                            @if($purchaseorder['discount']>0)
                                                <tr>
                                                    <td>{{trans('general.discount')}}</td>
                                                    <td class="text-right">{{amountFormat($purchaseorder['discount'])}}</td>
                                                </tr>@endif
                                            @if($purchaseorder['shipping']>0)
                                                <tr>
                                                    <td>{{trans('general.shipping')}}</td>
                                                    <td class="text-right">{{amountFormat($purchaseorder['shipping'])}}</td>
                                                </tr>
                                                @if($purchaseorder['ship_tax']>0)
                                                    <tr>
                                                        <td>{{trans('general.shipping_tax')}}
                                                            ({{trans('general.'.$purchaseorder['ship_tax_type'])}})
                                                        </td>
                                                        <td class="text-right">{{amountFormat($purchaseorder['ship_tax'])}}</td>
                                                    </tr>@endif
                                            @endif
                                            <tr>
                                                <td class="text-bold-800">{{trans('general.total')}}</td>
                                                <td class="text-bold-800 text-right">{{amountFormat($purchaseorder['total'])}}</td>
                                            </tr>
                                            <tr>
                                                <td>{{trans('general.payment_made')}}</td>
                                                <td class="pink text-right">(-) <span
                                                            id="payment_made">{{amountFormat($purchaseorder['pamnt'])}}</span>
                                                </td>
                                            </tr>
                                            <tr class="bg-grey bg-lighten-4">
                                                <td class="text-bold-800">{{trans('general.balance_due')}}</td>
                                                <td class="text-bold-800 text-right"
                                                    id="payment_due"> {{amountFormat($purchaseorder['total']-$purchaseorder['pamnt'])}}</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="text-center">
                                        <p>{{trans('general.authorized_person')}}</p>
                                        <img src="{{ Storage::disk('public')->url('app/public/img/signs/' . $purchaseorder->user->signature) }}"
                                             alt="signature" class="height-100 m-2"/>
                                        <h6>{{$purchaseorder->user->first_name}} {{$purchaseorder->user->last_name}}</h6>

                                    </div>
                                </div>
                            </div>
                        </div>

                    {!! custom_fields_view(2,$purchaseorder['id']) !!}

                    <!-- Invoice Footer -->
                        <div id="invoice-footer">
                            <div class="row">
                                <div class="col-md-7 col-sm-12">
                                    <h5>{{trans('general.payment_terms')}}</h5>
                                    <hr>
                                    <h5>{{@$purchaseorder->term->title}}</h5>
                                    <p>{!! @$purchaseorder->term->terms !!}</p>
                                </div>
                                <div class="col-md-5 col-sm-12 text-center">
                                    @if($purchaseorder['status']!='canceled')   <a href="#sendEmail" data-toggle="modal"
                                                                                   data-remote="false"
                                                                                   data-type="1"
                                                                                   data-type1="notification"
                                                                                   class="btn btn-primary btn-lg my-1 send_bill"><i
                                                class="fa fa-paper-plane-o"></i> {{trans('general.send')}}
                                    </a>@endif
                                </div>
                            </div>
                        </div>


                        <!--/ Invoice Footer -->
                        <div class="row mt-2">

                            <div class="col-md-12">
                                <p class="lead">{{trans('transactions.transactions')}}</p>
                                <table class="table table-bordered table-md table-striped">
                                    @if(isset($purchaseorder->transactions[0]))
                                        <thead>
                                        <th>#</th>
                                        <th>{{trans('transactions.payment_date')}}</th>
                                        <th class="">{{trans('transactions.method')}}</th>
                                        <th class="text-right">{{trans('transactions.debit')}}</th>
                                        <th class="text-right">{{trans('transactions.credit')}}</th>
                                        <th class="">{{trans('transactions.note')}}</th>
                                        </thead>     @endif
                                    <tbody id="transaction_activity">
                                    @foreach($purchaseorder->transactions as $transaction)
                                        <tr>
                                            <th scope="row">{{ $loop->iteration }}</th>
                                            <td>
                                                <p class="text-muted"><a
                                                            href="{{route('biller.print_payslip',[$transaction['id'],1,1])}}"
                                                            class="btn btn-blue btn-sm"><span class="fa fa-print"
                                                                                              aria-hidden="true"></span></a> {{$transaction['payment_date']}}
                                                </p>
                                            </td>
                                            <td class="">{{$transaction['method']}}</td>
                                            <td class="text-right">{{amountFormat($transaction['debit'])}}</td>
                                            <td class="text-right">{{numberFormat($transaction['credit'])}}</td>
                                            <td class="">{{$transaction['note']}}</td>

                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>


                        <div class="row mt-2">
                            <div class="col-12">
                                <p class="lead">{{trans('general.attachment')}}</p>
                                <pre>{{trans('general.allowed')}}:   {{$features['value1']}} </pre>
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
                            <div class="progress-bar bg-success" role="progressbar" style="width: 0%" aria-valuenow="0"
                                 aria-valuemin="0" aria-valuemax="100"></div>
                        </div>

                        <!-- The container for the uploaded files -->
                        <table id="files" class="files table table-striped mt-2">
                            @foreach($purchaseorder->attachment as $row)
                                <tr>
                                    <td><a data-url="{{route('biller.bill_attachment')}}?op=delete&id={{$row['id']}}"
                                           class="aj_delete red"><i class="btn-sm fa fa-trash"></i></a> <a
                                                href="{{ Storage::disk('public')->url('app/public/files/' . $row['value']) }}"
                                                class="purple"><i class="btn-sm fa fa-eye"></i> {{$row['value']}}</a>
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

    @php $invoice=$purchaseorder; @endphp
    @include("focus.modal.payment_model",array('category'=>9))
    @include("focus.modal.email_model",array('category'=>9))
    @include("focus.modal.sms_model",array('category'=>9))
    @include("focus.modal.status_model")
  {{--  @include("focus.modal.cancel_model")--}}
@endsection
@section('extra-style')

@endsection
@section('extra-scripts')
    {!! Html::style('focus/jq_file_upload/css/jquery.fileupload.css') !!}
    {{ Html::script('focus/jq_file_upload/js/jquery.fileupload.js') }}


    <script type="text/javascript">

        $('[data-toggle="datepicker"]').datepicker({
            autoHide: true,
            format: '{{config('core.user_date_format')}}'
        });
        $('[data-toggle="datepicker"]').datepicker('setDate', '{{date(config('core.user_date_format'))}}');

        $(function () {
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
            $(function () {
                'use strict';
                // Change this to the location of your server-side upload handler:
                var url = '{{route('biller.bill_attachment')}}';
                $('#fileupload').fileupload({
                    url: url,
                    dataType: 'json',
                    formData: {_token: "{{ csrf_token() }}", id: '{{$purchaseorder['id']}}', 'bill': 9},
                    done: function (e, data) {

                        $.each(data.result, function (index, file) {
                            $('#files').append('<tr><td><a data-url="{{route('biller.bill_attachment')}}?op=delete&id= ' + file.id + ' " class="aj_delete red"><i class="btn-sm fa fa-trash"></i></a> ' + file.name + ' </td></tr>');
                        });

                    },
                    progressall: function (e, data) {

                        var progress = parseInt(data.loaded / data.total * 100, 10);

                        $('#progress .progress-bar').css(
                            'width',
                            progress + '%'
                        );

                    }
                }).prop('disabled', !$.support.fileInput)
                    .parent().addClass($.support.fileInput ? undefined : 'disabled');
            });

            $(document).on('click', ".aj_delete", function (e) {
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
                    success: function (data) {
                        obj.closest('tr').remove();
                        obj.remove();
                    }
                });

            });
        });
    </script>

@endsection