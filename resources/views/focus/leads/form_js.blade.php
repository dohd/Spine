@section("after-scripts")
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    const config = {
        ajax: { headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } },
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
    };

    const Form = {
        lead: @json(@$lead),
        branches: @json($branches), 

        init() {
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#customer').select2({allowClear: true});
            $('#branch').select2({allowClear: true});

            $('.client-type').change(this.clientTypeChange);
            $('#customer').change(this.customerChange);
            $('#add-reminder').change(this.reminderChange);
            if (this.lead) {
                const lead = this.lead;
                $('#customer').val(lead.client_id).change();
                $('#branch').val(lead.branch_id);
                $('#date_of_request').datepicker('setDate', new Date(lead.date_of_request));
                $('#reminder_date').val(lead.reminder_date);
                $('#exact_date').val(lead.exact_date);
                ['reminder_date', 'exact_date'].forEach(v => $('#' + v).attr('disabled', false));
                if (lead.client_id == 0) {
                    ['payer-name', 'client_email', 'client_contact', 'client_address']
                    .forEach(v => $('#' + v).attr('readonly', false));
                    $('#colorCheck3').prop('checked', true);
                }
            } else {
                $('#customer').val('').change();
                $('#branch').val('').change();
            }
        },

        clientTypeChange() {
            if ($(this).val() == 'new') {
                $('#customer').attr('disabled', true).val('').change();
                $('#branch').attr('disabled', true).val('');
                ['payer-name', 'client_email', 'client_contact', 'client_address'].forEach(v => {
                    $('#'+v).attr('readonly', false).val('');
                });
            } else {
                $('#customer').attr('disabled', false).val('');
                $('#branch').attr('disabled', false).val('');
                ['payer-name', 'client_email', 'client_contact', 'client_address'].forEach(v => {
                    $('#'+v).attr('readonly', true).val('');
                });
            }
        },

        reminderChange(){
            if ($(this).is(":checked")) {
                $('#exact_date').attr('disabled', false).val('');
                $('#reminder_date').attr('disabled', false).val('');
            }else{
                $('#exact_date').attr('disabled', true).val('');
                $('#reminder_date').attr('disabled', true).val('');
            }
        },

        customerChange() {
            $('#branch option').remove();
            if ($(this).val()) {
                const customerBranches = Form.branches.filter(v => v.customer_id == $(this).val());
                customerBranches.forEach(v => $('#branch').append(`<option value="${v.id}">${v.name}</option>`));
                $('#branch').attr('disabled', false).val('');
            } else {
                $('#branch').attr('disabled', true);
            }
        },
    };

    $(() => Form.init());
</script>
@endsection