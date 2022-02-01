@extends('core.layouts.app')
@php
    $quote_type = $quote->bank_id ? 'PI' : 'Quote';
@endphp

@section('title', $quote_type . ' Approval')

@section('after-styles')
{!! Html::style('focus/jq_file_upload/css/jquery.fileupload.css') !!}
@endsection

@section('content')
<div class="app-content">
    <div class="content-wrapper">
        <div class="alert alert-danger alert-dismissible fade show d-none approve-alert" role="alert">
            <strong>Forbidden!</strong> Update customer details
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-2">
                <h4 class="content-header-title">{{ $quote_type }} Approval</h4>
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
                    @php
                        $approved_verified = ($quote->verified === "Yes" && $quote->status === 'approved');
                        $text = ($quote->verified === "Yes") ? 'This ' . $quote_type . ' is verified' : '';
                        if ($approved_verified) $text = 'This ' . $quote_type . ' is approved and verified';
                    @endphp
                    @if ($quote->verified === "Yes")
                        <div class="badge text-center white d-block m-1">
                            <span class="{{ $approved_verified ? 'bg-primary' : 'bg-success' }} round p-1">
                                <b>{{ $text }}</b>
                            </span>
                        </div>
                    @endif                    

                    <div id="invoice-customer-details" class="row pt-2">                        
                        <div class="col-md-6 col-sm-12 text-center text-md-left">
                            @php
                                $clientname = $quote->lead->client_name;
                                $branch = 'Head Office';
                                $address = $quote->lead->client_address;
                                $email = $quote->lead->client_email;
                                $cell = $quote->lead->client_contact;
                                if ($quote->client) {
                                    $clientname = $quote->client->company;						
                                    $branch = $quote->branch->name;
                                    $address = $quote->client->address;
                                    $email = $quote->client->email;
                                    $cell = $quote->client->phone;
                                }					
                            @endphp
                            <span class="text-muted"><b>{{ trans('invoices.bill_to') }}</b></span>
                            <ul class="px-0 list-unstyled">
                                <li><i>{{ $clientname }},</i></li>
                                <li><i>{{ $branch }},</i></li>
                                <li><i>{{ $address }},</i></li>
                                <li><i>{{ $email }},</i></li>
                                <li><i>{{ $cell }}</i></li>                                
                            </ul>
                            Client Ref: {{ $quote->client_ref }}
                        </div>
                        <div class="col-md-6 col-sm-12 text-center text-md-right">
                            @php
                                $tid = sprintf('%04d', $quote->tid);
                                $tid = $quote->bank_id ? '#PI-'.$tid : '#QT-'.$tid;
                            @endphp
                            <h2>{{ $tid }}</h2>
                            <h3>{{ '#Tkt-' . sprintf('%04d', $quote->lead->reference) }}</h3>
                            <p>                                
                                {{trans('quotes.invoicedate')}} : {{dateFormat($quote['invoicedate'])}}<br>
                                {{trans('quotes.invoiceduedate')}} : {{dateFormat($quote['invoiceduedate'])}}
                            </p>
                            <div class="row">
                                <div class="col">
                                    <br><hr>
                                    <p class="text-danger">{{ $quote->notes }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="invoice-items-details" class="pt-2">
                        <div class="row">
                            <div class="table-responsive col-sm-12">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{trans('products.product_des')}}</th>
                                            <th class="text-right">{{trans('products.price')}}</th>
                                            <th class="text-right">{{trans('products.qty')}}</th>
                                            <th class="text-right">{{trans('general.tax')}}</th>
                                            <th class="text-right">{{trans('general.subtotal')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($products as $product)
                                            @if ($product['a_type'] == 1)                                               
                                                <tr>
                                                    <td scope="row">{{ $product['numbering'] }}</td>
                                                    <td>
                                                        <p>{{$product['product_name']}}</p>
                                                        <p class="text-muted"> {!!$product['product_des'] !!} </p>
                                                    </td>
                                                    <td class="text-right">{{amountFormat($product['product_price'])}}</td>
                                                    <td class="text-right">{{ (int) $product['product_qty'] }} {{$product['unit']}}</td>
                                                    @php
                                                        $price = (float) $product->product_price;
                                                        $subtotal = (float) $product->product_subtotal;
                                                    @endphp
                                                    <td class="text-right">{{ amountFormat($subtotal - $price) }}
                                                        <span class="font-size-xsmall">({{ $quote->tax_id }}%)</span>
                                                    </td>
                                                    <td class="text-right">{{ amountFormat(intval($product->product_qty) * $subtotal) }}</td>
                                                </tr>
                                            @else
                                                <tr>
                                                    <td scope="row">{{ $product['numbering'] }}</td>
                                                    <td><p>{{$product['product_name']}}</p></td>
                                                    @for ($i = 0; $i < 4; $i++)
                                                        <td class="text-right"></td>                                                    
                                                    @endfor                                                    
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Approvals -->
                            <div class="col-md-7">
                                <p class="lead">Approval Details</p><hr>
                                <p>
                                    Approved By : <span class="text-danger mr-1">{{ $quote->approved_by }}</span> 
                                    Approved On : <span class=" text-danger mr-1">{{ dateFormat($quote->approved_date) }}</span> 
                                    Approval Method : <span class=" text-danger">{{ $quote->approved_method }}</span>
                                </p>
                                <p>
                                    Approval Note: <span class="text-danger mr-1">{{ $quote->approval_note }}</span> 
                                </p>
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
                                            <tr>
                                                <td>{{trans('general.tax')}}</td>
                                                <td class="text-right">{{amountFormat($quote['tax'])}}</td>
                                            </tr>
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
                    
                    <!-- Invoice Footer -->
                    <div id="invoice-footer">
                        <div class="row">
                            <!-- LPO Details -->
                            <div class="col-md-7 col-sm-12">
                                @isset($quote->lpo)
                                    <h3>LPO Details</h3>
                                    <p>
                                        LPO Date : <span class="text-danger mr-1">{{ dateFormat($quote->lpo->date) }}</span>
                                        LPO Number : <span class="text-danger mr-1">{{ $quote->lpo->lpo_no }}</span>                                     
                                        LPO Amount : <span class="text-danger">{{ number_format($quote->lpo->amount, 2) }}</span>                                                                    
                                    </p> 
                                    <p>
                                        LPO Remark : <span class="text-danger">{{ $quote->lpo->remark }}</span> 
                                    </p>                               
                               @endisset
                            </div>

                            <div class="col-md-5 col-sm-12 text-center">
                                @if ($quote->status !== 'cancelled') 
                                    <a href="#sendEmail" data-toggle="modal" data-remote="false" data-type="6" data-type1="proposal" class="btn btn-primary btn-lg my-1 send_bill">
                                        <i class="fa fa-paper-plane-o"></i> {{trans('general.send')}}
                                    </a>
                                @endif
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
                        @foreach ($quote->attachment as $row)
                            <tr>
                                <td>
                                    <a data-url="{{route('biller.bill_attachment')}}?op=delete&id={{$row['id']}}" class="aj_delete red">
                                        <i class="btn-sm fa fa-trash"></i>
                                    </a> 
                                    <a href="{{ Storage::disk('public')->url('app/public/files/' . $row['value']) }}" class="purple">
                                        <i class="btn-sm fa fa-eye"></i> {{$row['value']}}
                                    </a>
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
@include("focus.modal.lpo_model")
@include('focus.modal.sms_model', array('category'=>4))
@include('focus.modal.email_model', array('category'=>4))
@endsection

@section('extra-scripts')
{{ Html::script('focus/jq_file_upload/js/jquery.fileupload.js') }}

<script type="text/javascript">
    // check if previous page was pi page and add 'page=pi' querystring to current page
    if (document.referrer.includes('page=pi')) {
        const queryString = location.search;
        if (!queryString.includes('page=pi')) {
            location.href = location.href + queryString;
        }
    }
    
    // initialize datepicker
    $('.datepicker')
        .datepicker({ format: "{{ config('core.user_date_format') }}" })
        .datepicker('setDate', new Date());

    // On delete Quote
    $('.quote-delete').click(function(e) {
        if (confirm('Are you sure to delete this item ?')) {
            $(this).children('form').submit();
        }
    });

    // On cancel Quote
    $('.quote-cancel').click(function(e) {
        $(this).children('form').submit();
    });

    // On Approve Quote
    $('.quote-approve').click(function(e) {
        const customerId = @json($quote->customer_id);
        if (customerId) return;
        $(this).attr('href', '#');
        $('.approve-alert').removeClass('d-none');
    });

    // On Add LPO modal
    const lpos = @json($lpos);
    $('#pop_model_4').on('shown.bs.modal', function() { 
        const $modal = $(this);
        // on selecting lpo option set default values
        $modal.find("#lpo_id").change(function() {
            lpos.forEach(v => {
                if (v.id == $(this).val()) {
                    $modal.find('input[name=lpo_date]').val(v.date);
                    $modal.find('input[name=lpo_amount]').val(v.amount);
                    $modal.find('input[name=lpo_number]').val(v.lpo_no);
                }                
            });
        });
    });

    // On showing Approval Model
    $('#pop_model_1').on('shown.bs.modal', function() { 
        $form = $(this).find('#form-approve');

        // On clicking Mark As select dropdown
        $form.find('select[name=status]').click(function() {
            $form.find('label[for=approved-by]').text('Approved By');
            $form.find('label[for=approval-date]').text('Approval Date');
            $('#approvedby').attr('placeholder', 'Approved By');
            $('#approvedmethod').attr('disabled', false).off('mousedown');
            $('#approveddate').attr('readonly', false).off('mousedown');
            $('#btn_approve').text('Approve');

            if ($(this).val() === 'cancelled') {
                $form.find('label[for=approved-by]').text('Cancelled By');
                $form.find('label[for=approval-date]').text('Cancel Date');
                $('#approvedby').attr('placeholder', 'Called By');
                $('#btn_approve').text('Cancel');

                $('#approvedmethod').attr('disabled', true)
                    .on('mousedown', function(e) { e.preventDefault(); });          
                $('#approveddate').attr('readonly', true).datepicker('setDate', new Date())
                    .on('mousedown', function(e) { e.preventDefault(); });   
            }
        });
    });

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

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
            const url = "{{ route('biller.bill_attachment') }}"
            $('#fileupload')
                .fileupload({
                    url,
                    dataType: 'json',
                    formData: {
                        _token: "{{ csrf_token() }}",
                        id: "{{$quote['id ']}}",
                        bill: 4
                    },
                    done: function(e, data) {
                        $.each(data.result, function(index, file) {
                            const row = `<tr><td><a data-url="{{route('biller.bill_attachment')}}?op=delete&id=${file.id}" class="aj_delete red"><i class="btn-sm fa fa-trash"></i></a>${file.name}</td></tr>`;
                            $('#files').append(row);
                        });
                    },
                    progressall: function(e, data) {
                        var progress = parseInt(data.loaded / data.total * 100, 10);
                        $('#progress .progress-bar').css('width', progress + '%');
                    }
                })
                .prop('disabled', !$.support.fileInput)
                .parent().addClass($.support.fileInput ? undefined : 'disabled');
        });

        $(document).on('click', ".aj_delete", function(e) {
            e.preventDefault();
            var url = $(this).attr('data-url');
            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                success: function(data) {
                    $(this).closest('tr').remove();
                    $(this).remove();
                }
            });
        });
    });
</script>
@endsection
