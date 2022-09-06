<script>
    $('#AddMileStoneModal').on('shown.bs.modal', function() {
        $('[data-toggle="datepicker"]').datepicker({
            autoHide: true,
            format: '{{ config('core.user_date_format') }}'
        });
        $('.from_date').datepicker('setDate',
            '{{ dateFormat(date('Y-m-d', strtotime('-30 days', strtotime(date('Y-m-d'))))) }}');
        $('.from_date').datepicker({
            autoHide: true,
            format: '{{ date(config('core.user_date_format')) }}'
        });
        $('.to_date').datepicker('setDate', 'today');
        $('.to_date').datepicker({
            autoHide: true,
            format: '{{ date(config('core.user_date_format')) }}'
        });
        $('#color').colorpicker();
    });
    $("#submit-data_mile_stone").on("click", function(e) {
        e.preventDefault();
        var form_data = [];
        form_data['form'] = $("#data_form_mile_stone").serialize();
        form_data['url'] = $('#action-url').val();
        $('#AddMileStoneModal').modal('toggle');
        addObject(form_data, true);
    });
    $("#submit-data_log").on("click", function(e) {
        e.preventDefault();
        var form_data = [];
        form_data['form_name'] = 'data_form_log';
        form_data['form'] = $("#data_form_log").serialize();
        form_data['url'] = $('#action-url_5').val();
        addObject(form_data, true);
        $('#AddLogModal').modal('toggle');
    });
    $("#submit-data_note").on("click", function(e) {
        e.preventDefault();
        var form_data = [];
        form_data['form_name'] = 'data_form_note';
        form_data['form'] = $("#data_form_note").serialize();
        form_data['url'] = $('#action-url_6').val();
        addObject(form_data, true);
        $('#AddNoteModal').modal('toggle');
    });
    @include('focus.projects.adt.new_task_js')
    $(function() {
        'use strict';

        var slider = $('#progress');
        var textn = $('#prog');
        textn.text(slider.val() + '%');
        $(document).on('change', slider, function(e) {
            e.preventDefault();
            textn.text($('#progress').val() + '%');
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: '{{ route('biller.projects.update_status') }}',
                type: 'POST',
                data: {
                    'project_id': '{{ $project['id'] }}',
                    'r_type': '1',
                    'progress': $('#progress').val()
                },
                dataType: 'json',
                success: function(data) {

                    $('#description').html(data.description);
                    $('#task_title').html(data.name);
                    $('#employee').html(data.employee);
                    $('#assign').html(data.assign);
                    $('#priority').html(data.priority);
                }

            });
        });
        $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
            localStorage.setItem('project_tab', $(e.target).attr('href'));

            switch ($(e.target).attr('href')) {
                case '#tab_data3':
                    tasks();
                    break;
                case '#tab_data4':
                    project_log();
                    break;
                case '#tab_data6':
                    notes();
                    break;
                case '#tab_data7':
                    invoices();
                    break;
            }

        });
        var project_tab = localStorage.getItem('project_tab');
        if (project_tab) {
            $('a[href="' + project_tab + '"]').tab('show');
        }

        //log


        // Change this to the location of your server-side upload handler:
        var url = '{{ route('biller.project_attachment') }}';
        $('#fileupload').fileupload({
                url: url,
                dataType: 'json',
                formData: {
                    _token: "{{ csrf_token() }}",
                    id: '{{ $project['id'] }}',
                    'bill': 11
                },
                done: function(e, data) {

                    $.each(data.result, function(index, file) {
                        $('#files').append(
                            '<tr><td><a data-url="{{ route('biller.project_attachment') }}?op=delete&id= ' +
                            file.id +
                            ' " class="aj_delete red"><i class="btn-sm fa fa-trash"></i></a> ' +
                            file.name + ' </td></tr>');
                    });

                },
                progressall: function(e, data) {

                    var progress = parseInt(data.loaded / data.total * 100, 10);

                    $('#progress .progress-bar').css(
                        'width',
                        progress + '%'
                    );

                }
            }).prop('disabled', !$.support.fileInput)
            .parent().addClass($.support.fileInput ? undefined : 'disabled');


    });
    $('.summernote').summernote({
        height: 300,
        toolbar: [
            // [groupName, [list of button]]
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']],
            ['fullscreen', ['fullscreen']],
            ['codeview', ['codeview']]
        ],
        popover: {}
    });
    $(document).on('click', ".aj_delete", function(e) {
        e.preventDefault();
        var aurl = $(this).attr('data-url');
        var obj = $(this);
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url: aurl,
            type: 'POST',
            dataType: 'json',
            success: function(data) {
                obj.closest('tr').remove();
                obj.remove();
            }
        });

    });


    function trigger(data) {
        switch (data.t_type) {
            case 1:
                $('#m_' + data.meta).remove();
                break;

            case 2:
                $('.timeline').prepend(data.meta);
                break;

            case 3:
                $(data.row).prependTo("#tasks-table tbody");

                $("#data_form_task").trigger('reset');
                break;
            case 5:
                $(data.meta).prependTo("#log-table  tbody");


                $("#data_form_log").trigger('reset');
                break;

            case 6:
                $(data.meta).prependTo("#notes-table  tbody");


                $("#data_form_note").trigger('reset');
                break;
        }

    }

    function invoices() {
        if ($('#invoices-table_p tbody').is(":empty")) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var dataTable = $('#invoices-table_p').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {
                    @lang('datatable.strings')
                },
                ajax: {
                    url: '{{ route('biller.projects.invoices') }}?project_id={{ $project->id }}',
                    type: 'post',
                },
                columns: [{
                        data: 'DT_Row_Index',
                        name: 'id'
                    },
                    {
                        data: 'tid',
                        name: 'tid'
                    },
                    {
                        data: 'customer',
                        name: 'customer'
                    },
                    {
                        data: 'invoicedate',
                        name: 'invoicedate'
                    },
                    {
                        data: 'total',
                        name: 'total'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'invoiceduedate',
                        name: 'invoiceduedate'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        searchable: false,
                        sortable: false
                    }
                ],
                order: [
                    [0, "asc"]
                ],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: {
                    buttons: [

                        {
                            extend: 'csv',
                            footer: true,
                            exportOptions: {
                                columns: [0, 1]
                            }
                        },
                        {
                            extend: 'excel',
                            footer: true,
                            exportOptions: {
                                columns: [0, 1]
                            }
                        },
                        {
                            extend: 'print',
                            footer: true,
                            exportOptions: {
                                columns: [0, 1]
                            }
                        }
                    ]
                }
            });
            $('#invoices-table_p_wrapper').removeClass('form-inline');
        }
    }

    function notes() {
        if ($('#notes-table tbody').is(":empty")) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var dataTable = $('#notes-table').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {
                    @lang('datatable.strings')
                },
                ajax: {
                    url: '{{ route('biller.notes.get') }}?p={{ $project->id }}',
                    type: 'post',
                },
                columns: [{
                        data: 'DT_Row_Index',
                        name: 'id'
                    },
                    {
                        data: 'title',
                        name: 'title'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'user',
                        name: 'user'
                    },

                    {
                        data: 'actions',
                        name: 'actions',
                        searchable: false,
                        sortable: false
                    }

                ],
                order: [
                    [0, "asc"]
                ],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: {
                    buttons: [

                        {
                            extend: 'csv',
                            footer: true,
                            exportOptions: {
                                columns: [0, 1]
                            }
                        },
                        {
                            extend: 'excel',
                            footer: true,
                            exportOptions: {
                                columns: [0, 1]
                            }
                        },
                        {
                            extend: 'print',
                            footer: true,
                            exportOptions: {
                                columns: [0, 1]
                            }
                        }
                    ]
                }
            });
            $('#notes-table_wrapper').removeClass('form-inline');

        }
    }

    function tasks() {
        if ($('#tasks-table tbody').is(":empty")) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var dataTable = $('#tasks-table').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {
                    @lang('datatable.strings')
                },
                ajax: {
                    url: '{{ route('biller.tasks.get') }}?p={{ $project->id }}',
                    type: 'post',
                },
                columns: [{
                        data: 'tags',
                        name: 'tags'
                    },
                    {
                        data: 'start',
                        name: 'start'
                    },
                    {
                        data: 'duedate',
                        name: 'duedate'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },

                    {
                        data: 'actions',
                        name: 'actions',
                        searchable: false,
                        sortable: false
                    }
                ],
                order: [
                    [0, "asc"]
                ],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: {
                    buttons: [

                        {
                            extend: 'csv',
                            footer: true,
                            exportOptions: {
                                columns: [0, 1]
                            }
                        },
                        {
                            extend: 'excel',
                            footer: true,
                            exportOptions: {
                                columns: [0, 1]
                            }
                        },
                        {
                            extend: 'print',
                            footer: true,
                            exportOptions: {
                                columns: [0, 1]
                            }
                        }
                    ]
                }
            });
            $('#tasks-table_wrapper').removeClass('form-inline');
        }
    }

    function project_log() {
        if ($('#log-table tbody').is(":empty")) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var dataTable = $('#log-table').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {
                    @lang('datatable.strings')
                },
                ajax: {
                    url: '{{ route('biller.projects.log_history') }}?project_id={{ $project->id }}',
                    type: 'post',
                },
                columns: [{
                        data: 'DT_Row_Index',
                        name: 'id'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'user',
                        name: 'user'
                    },
                    {
                        data: 'value',
                        name: 'value'
                    }

                ],
                order: [
                    [0, "asc"]
                ],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: {
                    buttons: [

                        {
                            extend: 'csv',
                            footer: true,
                            exportOptions: {
                                columns: [0, 1]
                            }
                        },
                        {
                            extend: 'excel',
                            footer: true,
                            exportOptions: {
                                columns: [0, 1]
                            }
                        },
                        {
                            extend: 'print',
                            footer: true,
                            exportOptions: {
                                columns: [0, 1]
                            }
                        }
                    ]
                }
            });
            $('#log-table_wrapper').removeClass('form-inline');
        }
    }
</script>