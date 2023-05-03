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
            
            direct: @json(@$direct),
            excel: @json(@$excel),
            init() {
                $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
                $('#title').select2({
                    allowClear: true
                });
               
                $('.prospect-type').change(this.prospectTypeChange);
                $('.prospect-count').text(this.direct);

            },

           
            prospectTypeChange() {
                if ($(this).val() == 'direct') {

                    $('#title').attr('disabled', true).val('').change();
                    
                } else {
                    $('#title').attr('disabled', false).val('');
                   

                }
            },

        };

        $(() => Form.init());
    </script>
@endsection
