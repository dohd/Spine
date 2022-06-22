@extends('core.layouts.app')

@section('title',  'Issuance Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Issuance Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.issuance.partials.issuance-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body ">
                            {{ Form::open(['route' => 'biller.issuance.store', 'method' => 'POST']) }}
                                @include('focus.issuance.form')
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section("after-scripts")
<script type="text/javascript">
    $.ajaxSetup({headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }});

    $('.datepicker')
    .datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true,})
    .datepicker('setDate', new Date());

    // on qty change
    $('#stockTbl').on('change', '.qty', function() {
        const row = $(this).parents('tr');
        row.find('.ref').attr('required', false);
        row.find('.wh').attr('required', false);
        if ($(this).val() > 0) {
            row.find('.ref').attr('required', true);
            row.find('.wh').attr('required', true);
        }
        calcTotal();
    });

    function calcTotal() {
        let total = 0;
        let subtotal = 0;
        $('#stockTbl tbody tr').each(function() {
            const qty = $(this).find('.qty').val();
            if (qty > 0) {
                const price = $(this).find('.price').val();
                const amountInc = $(this).find('.amount').val();
                subtotal += price * qty;
                total += amountInc * qty;
            }
        });
        $('#subtotal').val(parseFloat(subtotal.toFixed(2)).toLocaleString());
        $('#total').val(parseFloat(total.toFixed(2)).toLocaleString());
        $('#tax').val(parseFloat((total-subtotal).toFixed(2)).toLocaleString());
    }
</script>
@endsection