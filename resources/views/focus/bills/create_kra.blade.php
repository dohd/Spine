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
    const config = {
        ajaxSetup: { 
            headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"} 
        },
        datepicker: {
            format: "{{config('core.user_date_format')}}", 
            autoHide: true,
        },
        supplierSelect2: {
            ajax: {
                url: "{{ route('biller.suppliers.select') }}",
                dataType: 'json',
                type: 'POST',
                quietMillis: 50,
                data: ({term}) => ({keyword: term}),
                processResults: function(data) {
                    return {results: data.map(v => ({id: v.id, text: v.name + ' : ' + v.email}))}; 
                }
            }
        }
    };

    const Form = {
        rowIndx: 0,
        tableRow: $('#billsTbl tbody tr:first').html(),

        init (config) {
            $.ajaxSetup(config.ajaxSetup);
            $('.datepicker').datepicker(config.datepicker).datepicker('setDate', new Date());
            $('#supplier').select2(config.supplierSelect2);

            $('#billsTbl').on('change', '.amount', this.amountChange);
            $('#billsTbl').on('click', '.del', this.deleteRow);
            $('#addRow').click(this.addRow);
        },

        addRow() {
            this.rowIndx++;
            const i = this.rowIndx;
            const html = Form.tableRow.replace(/-0/g, '-'+i);
            $('#billsTbl tbody').append('<tr>' + html + '</tr>');
        },

        amountChange() {
            const el = $(this);
            el.val(accounting.formatNumber(el.val()));
            Form.columnTotals();
        },

        deleteRow() {
            $(this).parents('tr').remove();
            Form.columnTotals();
        },

        columnTotals() {
            let total = 0;
            $('#billsTbl tbody tr').each(function() {
                const el = $(this);
                let amount = accounting.unformat(el.find('.amount').val());
                total += amount;
            });
            $('#total').val(accounting.formatNumber(total));
        }
    }

    $(() => Form.init(config));
</script>
@endsection