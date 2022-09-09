@extends ('core.layouts.app')

@section('title', 'Opening Balance Manage | Create')

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-body">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <form method="post" id="data_form">
                                <div class="row">
                                    <div class="col-sm-6 cmp-pnl">
                                        <div id="customerpanel" class="inner-cmp-pnl">
                                            <div class="form-group row">
                                                <div class="fcol-sm-12">
                                                    <h3 class="title">Product

                                                    </h3>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="frmSearch col-sm-12">
                                                    {{ Form::label('cst', 'Search Product', ['class' => 'caption']) }}
                                                    {{ Form::text('cst', null, ['class' => 'form-control round user-box', 'placeholder' => 'Enter Product Code or Name to Search', 'id' => 'suppliers-box', 'data-section' => 'suppliers', 'autocomplete' => 'off']) }}
                                                    <div id="suppliers-box-result"></div>
                                                </div>
                                            </div>
                                            <div id="customer">
                                                <div class="clientinfo">Product Details
                                                    <hr>
                                                    <div id="customer_name"></div>
                                                </div>
                                                <div class="clientinfo">
                                                    <div id="customer_address1"></div>
                                                </div>
                                                <div class="clientinfo">
                                                    <div id="customer_phone"></div>
                                                </div>
                                                <hr>
                                                <div id="customer_pass"></div>
                                                <hr>
                                            </div>
                                            {{ Form::hidden('supplier_id', '0', ['id' => 'customer_id']) }}
                                        </div>
                                    </div>
                                    <div class="col-sm-6 cmp-pnl">
                                        <div class="inner-cmp-pnl">
                                            <div class="form-group row">
                                                <div class="col-sm-12">
                                                    <h3 class="title">Details</h3>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-sm-6"><label for="invocieno" class="caption">Transaction
                                                        ID</label>
                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-file-text-o"
                                                                aria-hidden="true"></span>
                                                        </div>
                                                        {{ Form::number('tid', @$last_invoice->tid + 1, ['class' => 'form-control round', 'placeholder' => trans('purchaseorders.tid')]) }}
                                                    </div>
                                                </div>
                                                <div class="col-sm-6"><label for="invocieno"
                                                        class="caption">{{ trans('general.reference') }}</label>
                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-bookmark-o"
                                                                aria-hidden="true"></span>
                                                        </div>
                                                        {{ Form::text('refer', null, ['class' => 'form-control round', 'placeholder' => trans('general.reference')]) }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-sm-6"><label for="invociedate" class="caption">Transaction
                                                        Date</label>
                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-calendar4"
                                                                aria-hidden="true"></span>
                                                        </div>
                                                        {{ Form::text('invoicedate', null, ['class' => 'form-control round required', 'placeholder' => trans('purchaseorders.invoicedate'), 'data-toggle' => 'datepicker', 'autocomplete' => 'false']) }}
                                                    </div>
                                                </div>
                                                <div class="col-sm-6"><label for="invocieno" class="caption">Qty</label>
                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-bookmark-o"
                                                                aria-hidden="true"></span>
                                                        </div>
                                                        {{ Form::text('refer', null, ['class' => 'form-control round', 'placeholder' => 'Qty']) }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-sm-12">
                                                    <label for="toAddInfo"
                                                        class="caption">{{ trans('general.note') }}</label>
                                                    {{ Form::textarea('notes', null, ['class' => 'form-control round', 'placeholder' => trans('general.note'), 'rows' => '2']) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" value="new_i" id="inv_page">
                                <input type="hidden" value="{{ route('biller.purchaseorders.store') }}" id="action-url">
                                <input type="hidden" value="search" id="billtype">
                                <input type="hidden" value="0" name="counter" id="ganak">
                                <input type="hidden" value="{{ @$tax_format }}" name="tax_format_static" id="tax_format">
                                <input type="hidden" value="{{ @$tax_format_type }}" name="tax_format"
                                    id="tax_format_type">
                                <input type="hidden" value="{{ @$tax_format_id }}" name="tax_id" id="tax_format_id">
                                <input type="hidden" value="{{ @$discount_format }}" name="discount_format"
                                    id="discount_format">
                                @if (@$defaults[4][0]->ship_tax['id'] > 0)
                                    <input type='hidden' value='{{ numberFormat($defaults[4][0]->ship_tax['value']) }}'
                                        name='ship_rate' id='ship_rate'><input type='hidden'
                                        value='{{ $defaults[4][0]->ship_tax['type2'] }}' name='ship_tax_type'
                                        id='ship_taxtype'>
                                @else
                                    <input type='hidden' value='{{ numberFormat(0) }}' name='ship_rate'
                                        id='ship_rate'><input type='hidden' value='none' name='ship_tax_type'
                                        id='ship_taxtype'>
                                @endif
                                <input type="hidden" value="0" name="ship_tax" id="ship_tax">
                                <input type="hidden" value="0" id="custom_discount">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- @include('focus.modal.supplier') --}}
@endsection
@section('extra-scripts')
    <script type="text/javascript">
        $(function() {
            $('[data-toggle="datepicker"]').datepicker({
                autoHide: true,
                format: '{{ config('core.user_date_format') }}'
            });
            $('[data-toggle="datepicker"]').datepicker('setDate', '{{ date(config('core.user_date_format')) }}');
            editor();
        });
    </script>
@endsection
