@extends ('core.layouts.app')

@section('title', 'KRA | Bills Payment Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">KRA Bill</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.bills.partials.bills-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::open(['route' => 'biller.bills.store_kra', 'method' => 'POST']) }}
                        @include('focus.bills.kra_form')
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

    // datepicker
    $('.datepicker').datepicker({format: "{{config('core.user_date_format')}}", autoHide: true})
    .datepicker('setDate', new Date());

    // Load suppliers
    $('#supplier').select2({
        ajax: {
            url: "{{ route('biller.suppliers.select') }}",
            dataType: 'json',
            type: 'POST',
            quietMillis: 50,
            data: ({term}) => ({keyword: term}),
            processResults: function(data) {
                return {results: data.map(v => ({id: v.id, text: v.name + ' : ' + v.email}))}; 
            },
        }
    });

    // format amount on change
    $('#billsTbl').on('change', '.amount', function() {
        $(this).val(accounting.formatNumber($(this).val()));
        calcTotal();
    });

    // delete row
    $('#billsTbl').on('click', '.del', function() {
        $(this).parents('tr').remove();
        calcTotal();
    });

    // add row 
    let rowId = 0;
    const rowHmtl = $('#billsTbl tbody tr:first').html();
    $('.add-row').click(function() {
        rowId++;
        const html = rowHmtl.replace(/-0/g, '-'+rowId);
        $('#billsTbl tbody').append('<tr>' + html + '</tr>');
    });

    // calc totals
    function calcTotal() {
        let total = 0;
        $('#billsTbl tbody tr').each(function() {
            let amount = accounting.unformat($(this).find('.amount').val());
            total += amount;
        });
        $('#total').val(accounting.formatNumber(total));
    }
</script>
@endsection