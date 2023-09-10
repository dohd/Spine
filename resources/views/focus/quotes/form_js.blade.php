@section("after-scripts")
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    const config = {
        ajax: { headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } },
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
    };

    const Form = {
        initRowHTML: null,

        init() {
            $('#quote_type').change(Form.onChangeQuoteType).change();
            $('#add_product').click(Form.onClickAddProduct);
            $('#table_container').on('click', '.rem', Form.onClickRemoveProduct);
        },

        onChangeQuoteType() {
            $.post("{{ route('biller.quotes.quote_type') }}", {quote_type: $(this).val()}, data => {
                $('#table_container').html(data);
                $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
                Form.initRowHTML = $('#quoteTbl tbody tr:first').html();
            });
        },

        onClickAddProduct() {
            $('#quoteTbl tbody').append(`<tr>${Form.initRowHTML}</tr>`);
        },

        onClickRemoveProduct() {
            tr = $(this).parents('tr');
            if (tr.siblings().length) tr.remove();
        },
    };

    $(Form.init);
</script>
@endsection