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
                $('.direct-prospects').attr('hidden',false);
                $('#title').change(this.callListChange);
                let count = $('#directprospectcount').text();
                $('#prospects_number').val(count);
            },

           
            prospectTypeChange() {
                if ($(this).val() == 'direct') {
                    let count = $('#directprospectcount').text();
                    $('#prospects_number').val(count);
                    $('#title').attr('disabled', true).val('').change();
                    $('.direct-prospects').attr('hidden',false).change();
                } else {
                    $('#title').attr('disabled', false).val('');
                    $('.direct-prospects').attr('hidden',true);
                }
            },

            callListChange(){
                let count = $('#title option:selected').attr('count');
                
                $('#prospects_number').val(count);
            }

        };

        $(() => Form.init());
    </script>
@endsection
