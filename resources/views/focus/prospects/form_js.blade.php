@section("after-scripts")
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    const config = {
        ajax: { headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } },
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
    };

    const Form = {
        prospect: @json(@$prospect),
        branches: @json($branches), 

        init() {
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            if (this.prospect) {
 
                $('#reminder_date').datepicker('setDate', new Date(this.prospect.reminder_date));
            }
        },


      
    };

    $(() => Form.init());
</script>
@endsection