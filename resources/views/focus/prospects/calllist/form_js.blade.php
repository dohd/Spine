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
            
            init() {
                $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
                $('#group_title').select2({allowClear: true});
                $('.prospect-type').change(this.prospectTypeChange);
            },

            prospectTypeChange() {
            if ($(this).val() == 'direct') {
                $('#group_title').attr('disabled', true).attr('disabled', true).val('').change();
                
            } else {
                $('#group_title').attr('disabled', false).val('');
                
            }
        },

        };

        $(() => Form.init());
    </script>
@endsection
