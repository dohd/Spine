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
                $('#title').change(this.callListChange);
              
            },
            callListChange(){
                let count = $('#title option:selected').attr('count');
                
                $('#prospects_number').val(count);
            }

        };

        $(() => Form.init());
    </script>
@endsection
