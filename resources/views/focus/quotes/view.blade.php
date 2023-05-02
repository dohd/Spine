@extends('core.layouts.app')
@php
    $quote_type = $quote->bank_id ? 'Proforma Invoice' : 'Quote';
    $prefixes = prefixesArray(['quote', 'proforma_invoice', 'lead'], $quote->ins);
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

        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">{{ $quote_type }} Approval</h4>
            </div>
            <div class="content-header-right col-6">
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
                    @if ($quote->verified == "Yes" || $quote->status == 'approved')
                        @php
                            $text = "{$quote_type} is approved";
                            if ($quote->verified == 'Yes') $text .= ' and verified';
                            $approved_verified = ($quote->verified == "Yes" && $quote->status == 'approved');
                        @endphp
                        <div class="badge text-center white d-block m-1">
                            <span class="{{ $approved_verified ? 'bg-primary' : 'bg-success' }} round p-1">
                                <b>{{ $text }}</b>
                            </span>
                        </div>
                    @endif                    

                    <div id="invoice-customer-details" class="row pt-2">                        
                        <div class="col-6 text-center text-md-left">
                            @php
                                $clientname = $quote->lead? $quote->lead->client_name : '';
                                $branch = '';
                                $address = $quote->lead? $quote->lead->client_address : '';
                                $email = $quote->lead? $quote->lead->client_email : '';
                                $cell = $quote->lead? $quote->lead->client_contact : '';
                                if ($quote->customer) {
                                    $clientname = $quote->customer->company;						
                                    $address = $quote->customer->address;
                                    $email = $quote->customer->email;
                                    $cell = $quote->customer->phone;
                                    $branch = $quote->branch? $quote->branch->name : '';
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
                            Client Ref: {{ $quote->customer_ref }}
                        </div>
                        <div class="col-md-6 col-sm-12 text-center text-md-right">
                            <h2>
                                {{ gen4tid($quote->bank_id? "{$prefixes[1]}-" : "{$prefixes[0]}-", $quote->tid)}}{{ $quote->revision }}
                                {{ !$quote->is_repair? 'Maintenance' : '' }}
                            </h2>
                            <h3>{{ $quote->lead? gen4tid("{$prefixes[2]}-", $quote->lead->reference) : '' }}</h3>
                            <div class="row">
                                <div class="col">
                                    <hr>
                                    <p class="text-primary h4">{{ $quote->notes }}</p>
                                    <p>Date: {{ dateFormat($quote->date) }}</p>
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
                                            <th class="text-right">{{trans('products.qty')}}</th>                                          
                                            <th class="text-right">Product Rate</th>
                                            <th class="text-right">{{trans('general.tax')}}</th>
                                            <th class="text-right">Product Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($quote->products as $item)
                                            @if ($item->a_type == 1)                                               
                                                <tr class="{{ !$item->misc ?: 'text-danger' }}">
                                                    <td scope="row">{{ $item['numbering'] }}</td>
                                                    <td>
                                                        <p>{{$item['product_name']}}</p>
                                                        <p class="text-muted"> {!! $item['product_des'] !!} </p>
                                                    </td>
                                                    <td class="text-right">{{ $item->misc? +$item->estimate_qty : +$item['product_qty'] }} {{$item['unit']}}</td>
                                                    @if ($quote->currency)
                                                        <td class="text-right">{{ amountFormat($item->product_subtotal, $quote->currency->id) }}</td>
                                                        <td class="text-right">
                                                            {{ amountFormat(($item->product_price - $item->product_subtotal) * $item->product_qty, $quote->currency->id) }}
                                                            <span class="font-size-xsmall">({{ +$item->tax_rate }}%)</span>
                                                        </td>
                                                        <td class="text-right">{{ amountFormat($item->product_qty * $item->product_price, $quote->currency->id) }}</td>
                                                    @else
                                                        <td class="text-right">{{ numberFormat($item->product_subtotal) }}</td>
                                                        <td class="text-right">
                                                            {{ numberFormat(($item->product_price - $item->product_subtotal) * $item->product_qty) }}
                                                            <span class="font-size-xsmall">({{ +$item->tax_rate }}%)</span>
                                                        </td>
                                                        <td class="text-right">{{ numberFormat($item->product_qty * $item->product_price) }}</td>
                                                    @endif
                                                </tr>
                                            @else
                                                <tr>
                                                    <td scope="row">{{ $item['numbering'] }}</td>
                                                    <td><p>{{ $item['product_name'] }}</p></td>
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
                                @if ($quote->status != 'pending')
                                    @if ($quote->status == 'cancelled')
                                        <p>
                                            Cancelled By : <span class="text-danger mr-1">{{ $quote->approved_by }}</span>
                                            Cancelled On : <span class=" text-danger mr-1">{{ dateFormat($quote->approved_date) }}</span> 
                                        </p>
                                        Cancel Note:
                                    @else
                                        <p>
                                            Approved By : <span class="text-danger mr-1">{{ $quote->approved_by }}</span> 
                                            Approved On : <span class=" text-danger mr-1">{{ dateFormat($quote->approved_date) }}</span> 
                                            Approval Method : <span class=" text-danger">{{ $quote->approved_method }}</span>
                                        </p>
                                        Approval Note:                                    
                                    @endif
                                    <div class="text-danger">{!! $quote->approval_note !!}</div> 
                                @endif                             
                            </div>

                            <div class="col-md-5 col-sm-12">
                                <p class="lead">{{trans('general.summary')}}</p>
                                <div class="table-responsive">
                                    <table class="table">
                                        <tbody>
                                            @if ($quote->currency)
                                                <tr>
                                                    <td>{{trans('general.subtotal')}}</td>
                                                    <td class="text-right">{{amountFormat($quote['subtotal'], $quote->currency->id)}}</td>
                                                </tr>
                                                <tr>
                                                    <td>{{trans('general.tax')}}</td>
                                                    <td class="text-right">{{amountFormat($quote['tax'], $quote->currency->id)}}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-bold-800">{{trans('general.total')}}</td>
                                                    <td class="text-bold-800 text-right">{{amountFormat($quote['total'], $quote->currency->id)}}</td>
                                                </tr>
                                            @else
                                                <tr>
                                                    <td>{{trans('general.subtotal')}}</td>
                                                    <td class="text-right">{{numberFormat($quote['subtotal'])}}</td>
                                                </tr>
                                                <tr>
                                                    <td>{{trans('general.tax')}}</td>
                                                    <td class="text-right">{{numberFormat($quote['tax'])}}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-bold-800">{{trans('general.total')}}</td>
                                                    <td class="text-bold-800 text-right">{{numberFormat($quote['total'])}}</td>
                                                </tr>
                                            @endif
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
                                        LPO Amount : <span class="text-danger">{{ numberFormat($quote->lpo->amount) }}</span>                                                                    
                                    </p> 
                                    <p>LPO Remark : <span class="text-danger">{{ $quote->lpo->remark }}</span></p>
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
    $invoice = $quote; 
@endphp
@include("focus.modal.quote_status_model")
@include("focus.modal.lpo_model")
@include('focus.modal.sms_model', ['category' => 4])
@include('focus.modal.email_model', ['category' => 4])
@endsection

@section('extra-scripts')
{{ Html::script('focus/jq_file_upload/js/jquery.fileupload.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script type="text/javascript">
    // initialize editor
    editor();

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }
    });
    
    // initialize datepicker
    $('.datepicker')
    .datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    .datepicker('setDate', new Date());

    // on delete Quote
    $('.quote-delete').click(function() {
        const form = $(this).children('form');
        swal({
            title: 'Are You  Sure?',
            icon: "warning",
            buttons: true,
            dangerMode: true,
            showCancelButton: true,
        }, () => form.submit());
    });

    // on cancel Quote
    $('.quote-cancel').click(function() {
        $(this).children('form').submit();
    });

    // On Approve Quote
    $('.quote-approve').click(function(e) {
        const customerId = @json($quote->customer_id);
        if (!customerId) {
            $(this).attr('href', '#');
            $('.approve-alert').removeClass('d-none');
        }
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
        form = $(this).find('#form-approve');
        $('.aprv-status').click(function() {
            form.find('label[for=approved-by]').text('Approved By');
            form.find('label[for=approval-date]').text('Approval Date');
            if ($(this).val() == 'cancelled') {
                form.find('label[for=approved-by]').text('Cancelled By');
                form.find('label[for=approval-date]').text('Cancel Date');
                form.find('.aprv-by').attr('placeholder', 'Cancelled By');
                form.find('.aprv-method').val('').attr('disabled', true);
            } else {
                form.find('.aprv-by').attr('placeholder', 'Approved By');
                form.find('.aprv-method').attr('disabled', false);
                $('#btn_approve').text('Approve');
            }
        });
    });
</script>
@endsection