{{-- add task --}}
$('#AddTaskModal').on('shown.bs.modal', function () {
    $('[data-toggle="datepicker"]').datepicker({autoHide: true, format: '{{ config('core.user_date_format') }}'});
    $('.from_date').datepicker('setDate', 'today');
    $('.from_date').datepicker({autoHide: true, format: '{{ date(config('core.user_date_format')) }}'});
    $('.to_date').datepicker('setDate', '{{ dateFormat(date('Y-m-d', strtotime('+30 days', strtotime(date('Y-m-d'))))) }}');
    $('.to_date').datepicker({autoHide: true, format: '{{ date(config('core.user_date_format')) }}'});
    $("#tags").select2();
    $("#employee").select2();
    $("#projects").select2();
    $('#color_t').colorpicker();
});

{{-- submit task --}}
$("#submit-data_tasks").on("click", function() {
    event.preventDefault();
    var form_data = {};
    form_data['form'] = $("#data_form_task").serialize();
    form_data['url'] = $('#action-url_task').val();
    $('#AddTaskModal').modal('toggle');
    addObject(form_data, true);
});

{{-- view task --}}
$(document).on('click', '.view_task', function() {
    const url = "{{ route('biller.tasks.load') }}";
    const task_id = $(this).attr('data-id');
    $.post(url, {task_id}, data => {
        $('#t_name').html(data.name);
        $('#t_start').html(data.start)
        $('#t_end').html(data.duedate);
        $('#t_status').html(data.status);
        $('#t_status_list').html(data.status_list);
        $('#t_status_list').html(data.status_list);
        $('#t_creator').html(data.creator);
        $('#t_assigned').html(data.assigned);
        $('#ts_description').html(data.short_desc);
        $('#t_description').html(data.description);
    });
});
