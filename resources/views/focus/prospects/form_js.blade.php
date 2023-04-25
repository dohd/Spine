@section('after-scripts')
    {{ Html::script('focus/js/select2.min.js') }}
    <script type="text/javascript">
        const config = {
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }
            },
            date: {
                format: "{{ config('core.user_date_format') }}",
                autoHide: true
            },
        };

        const Form = {
            prospect: @json(@$prospect),
            date: @json(@$remarks->reminder_date),
            remark: @json(@$remarks->remarks),
            init() {
                $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
                if (this.prospect) {
                    $('#reminder_date').datepicker('setDate', new Date(this.date));
                    $('#remarks').val(this.remark);
                
                } else {
                    $('#remarks').val('');
                    $('#reminder_date').datepicker('setDate', new Date());
                }

            },



        };

        $(() => Form.init());
    </script>
@endsection
